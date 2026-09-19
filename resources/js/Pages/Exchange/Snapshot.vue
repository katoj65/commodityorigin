<script setup>
import { ref, computed } from 'vue';
import { ElMessage } from 'element-plus';
import {
    Grid,
    List,
    CircleCheckFilled,
    LocationFilled,
    Shop,
    Tickets,
    Files,
    Notebook,
    SwitchButton,
    Coin,
    Lock,
    StarFilled,
    ArrowRight,
    OfficeBuilding,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

defineProps({
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
});

/* ── Dummy exchange dataset ─────────────────────────────────────────── */
const lots = [
    { id: 'LOT-000124', name: 'Uganda Robusta', origin: 'Uganda', region: 'Mukono Basin', grade: 'Screen 18', processing: 'Natural', qtyKg: 5000, pricePerKg: 4.20, seller: 'Uganda Coffee Traders', sellerType: 'Licensed Exporter', cupScore: 83.5, moisture: '11.8%', incoterm: 'FOB Mombasa', eudr: true, actionType: 'trade' },
    { id: 'LOT-000231', name: 'Ethiopia Arabica', origin: 'Ethiopia', region: 'Sidama / Yirgacheffe', grade: 'Grade 1', processing: 'Washed', qtyKg: 2000, pricePerKg: 6.80, seller: 'Ethiopian Coffee Exporters', sellerType: 'Direct Cooperative', cupScore: 88.0, moisture: '10.9%', incoterm: 'FOB Djibouti', eudr: true, actionType: 'trade' },
    { id: 'LOT-000315', name: 'Mt. Elgon Arabica AA', origin: 'Uganda', region: 'Mt. Elgon / Bugisu', grade: 'Grade AA', processing: 'Washed', qtyKg: 19200, pricePerKg: 5.15, seller: 'Bugisu High Altitude Coop', sellerType: 'Cooperative Alliance', cupScore: 86.5, moisture: '11.2%', incoterm: 'FOB Mombasa', eudr: true, actionType: 'trade' },
    { id: 'LOT-000412', name: 'Mukono Fine Robusta', origin: 'Uganda', region: 'Mukono Basin', grade: 'Screen 18', processing: 'Natural', qtyKg: 38400, pricePerKg: 3.95, seller: 'Kyagalanyi Central Mill', sellerType: 'Dry Mill Processor', cupScore: 82.0, moisture: '11.8%', incoterm: 'FOB Mombasa', eudr: true, actionType: 'trade' },
    { id: 'LOT-000508', name: 'Rwenzori Snowpeaks', origin: 'Uganda', region: 'Rwenzori', grade: 'Grade 1', processing: 'Natural', qtyKg: 9600, pricePerKg: 4.70, seller: 'Kasese Agronomy Union', sellerType: 'Producer Group', cupScore: 85.0, moisture: '11.4%', incoterm: 'EXW Kampala Mill', eudr: true, actionType: 'trade' },
    { id: 'LOT-000624', name: 'Kenya Nyeri AA Microlot', origin: 'Kenya', region: 'Mt. Kenya Highlands', grade: 'Grade AA', processing: 'Washed', qtyKg: 2400, pricePerKg: 7.90, seller: 'Nyeri Farmers Union', sellerType: 'Cooperative', cupScore: 89.2, moisture: '10.8%', incoterm: 'FOB Mombasa', eudr: true, actionType: 'auction' },
    { id: 'LOT-000780', name: 'Kibale Screen 15 Robusta', origin: 'Uganda', region: 'Mukono Basin', grade: 'Screen 15', processing: 'Natural', qtyKg: 12000, pricePerKg: 3.85, seller: 'Mubende Origin Shippers', sellerType: 'Exporter', cupScore: 80.5, moisture: '12.0%', incoterm: 'FOB Mombasa', eudr: false, actionType: 'trade' },
    { id: 'LOT-000845', name: 'Rwanda Gisenyi Bourbon', origin: 'Rwanda', region: 'Lake Kivu', grade: 'Grade 1', processing: 'Honey', qtyKg: 3600, pricePerKg: 6.25, seller: 'Kivu Specialty Exporters', sellerType: 'Certified Exporter', cupScore: 87.0, moisture: '11.1%', incoterm: 'FOB Mombasa', eudr: true, actionType: 'trade' },
];

const marketMonitor = [
    { code: 'LOT-000315', label: 'Bugisu AA', tag: 'Offer', value: '$5.05' },
    { code: 'LOT-000624', label: 'Nyeri AA', tag: 'Bid', value: '$7.90' },
    { code: 'ESC-892', label: 'Contract', tag: '', value: '' },
];

const tabs = [
    { key: 'market', label: 'Market', icon: Shop, count: 128 },
    { key: 'offers', label: 'Offers', icon: Tickets, count: 46 },
    { key: 'rfqs', label: 'RFQs', icon: Files, count: 18 },
    { key: 'auctions', label: 'Auctions', icon: Notebook, count: 12 },
    { key: 'mytrades', label: 'My Trades', icon: SwitchButton, count: 5 },
];

const incotermOptions = [
    { label: 'FOB Port of Mombasa (Standard)', modifier: 0 },
    { label: 'CIF Jebel Ali (Dubai) +$0.12/kg', modifier: 0.12 },
    { label: 'CIF Rotterdam (EU) +$0.18/kg', modifier: 0.18 },
    { label: 'EXW Jinja Dry Mill -$0.08/kg', modifier: -0.08 },
];
const sortOptions = ['Relevance', 'Price: Low to High', 'Price: High to Low', 'Quantity', 'Origin'];

const activeTab = ref('market');
const viewMode = ref('grid');
const sortBy = ref(sortOptions[0]);

const filteredLots = computed(() => {
    const result = [...lots];

    if (sortBy.value === 'Price: Low to High') result.sort((a, b) => a.pricePerKg - b.pricePerKg);
    else if (sortBy.value === 'Price: High to Low') result.sort((a, b) => b.pricePerKg - a.pricePerKg);
    else if (sortBy.value === 'Quantity') result.sort((a, b) => b.qtyKg - a.qtyKg);
    else if (sortBy.value === 'Origin') result.sort((a, b) => a.origin.localeCompare(b.origin));

    return result;
});

/* ── Order Execution Desk ──────────────────────────────────────────── */
const deskModes = [
    { key: 'buy', label: 'Quick Buy', priceLabel: 'Spot Execution Price', action: 'Execute Escrow Spot Order', multiplier: 1 },
    { key: 'offer', label: 'Offer', priceLabel: 'Counter-Offer Limit ($/kg)', action: 'Submit Binding Counter-Offer', multiplier: 0.96 },
    { key: 'bid', label: 'Auction Bid', priceLabel: 'Auction Bid Ceiling ($/kg)', action: 'Place Competitive Bid', multiplier: 1.02 },
];

const selectedLot = ref(lots[0]);
const deskMode = ref('buy');
const deskQty = ref(selectedLot.value.qtyKg);
const deskPrice = ref(selectedLot.value.pricePerKg);
const deskIncoterm = ref(incotermOptions[0]);

const currentDeskMode = computed(() => deskModes.find((m) => m.key === deskMode.value));

function selectLot(lot) {
    selectedLot.value = lot;
    deskQty.value = lot.qtyKg;
    applyDeskModePrice();
}

function setDeskMode(modeKey) {
    deskMode.value = modeKey;
    applyDeskModePrice();
}

function applyDeskModePrice() {
    const mode = deskModes.find((m) => m.key === deskMode.value);
    deskPrice.value = Number((selectedLot.value.pricePerKg * mode.multiplier).toFixed(2));
}

function setDeskQty(qty) {
    deskQty.value = qty;
}

const bagCount = computed(() => Math.round(deskQty.value / 60));
const coffeeSubtotal = computed(() => deskQty.value * deskPrice.value);
const escrowFee = computed(() => coffeeSubtotal.value * 0.0075);
const freightTotal = computed(() => deskQty.value * deskIncoterm.value.modifier);
const grandTotal = computed(() => Math.max(0, coffeeSubtotal.value + escrowFee.value + freightTotal.value));

const usd = (value) => `$${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

function executeDeskOrder() {
    ElMessage.success(`${currentDeskMode.value.action} — Lot #${selectedLot.value.id}, ${deskQty.value.toLocaleString()} kg @ $${deskPrice.value.toFixed(2)}/kg (dummy preview).`);
}

/* ── Lot dossier / offer dialogs (dummy) ───────────────────────────── */
const dossierOpen = ref(false);
const dossierLot = ref(null);
const offerOpen = ref(false);
const offerLot = ref(null);
const offerPrice = ref(0);
const offerQty = ref(0);
const rfqOpen = ref(false);
const aiOpen = ref(false);
const aiDraft = ref('Prepare purchase contract for #LOT-000124');

function openDossier(lot) {
    dossierLot.value = lot;
    dossierOpen.value = true;
}

function openOffer(lot) {
    offerLot.value = lot;
    offerPrice.value = Number((lot.pricePerKg * 0.96).toFixed(2));
    offerQty.value = lot.qtyKg;
    offerOpen.value = true;
}

function submitOffer() {
    offerOpen.value = false;
    ElMessage.success(`Counter-offer of $${offerPrice.value}/kg for ${offerQty.value.toLocaleString()} kg submitted (dummy preview).`);
}

function handleBid(lot) {
    selectLot(lot);
    setDeskMode('bid');
    ElMessage.info(`Lot #${lot.id} loaded into the Auction Bid desk.`);
}

function handleInstantBuy(lot) {
    selectLot(lot);
    setDeskMode('buy');
}

function broadcastRfq() {
    rfqOpen.value = false;
    ElMessage.success('RFQ broadcast to 128 registered mills and exporters (dummy preview).');
}
</script>

<template>
    <OuterLayout title="Coffee Exchange">
        <div class="wp-exchange bg-white min-h-screen">
            <!-- Sub-navigation tabs & trade actions -->
            <div class="border-b border-slate-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 md:px-8 flex flex-wrap items-center justify-between gap-2">
                    <nav class="flex items-center gap-1 -mb-px overflow-x-auto py-1">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition"
                            :class="activeTab === tab.key ? 'border-slate-950 text-slate-950' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                            @click="activeTab = tab.key"
                        >
                            <el-icon :size="13"><component :is="tab.icon" /></el-icon>
                            {{ tab.label }}
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-[10px] font-semibold border border-slate-200">{{ tab.count }}</span>
                        </button>
                    </nav>
                    <div class="hidden sm:flex items-center gap-2 py-2">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition" @click="rfqOpen = true">
                            <el-icon :size="12"><Files /></el-icon> Create RFQ
                        </button>
                        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-950 hover:bg-slate-800 rounded-lg shadow-sm transition" @click="ElMessage.info('Seller lot submission is available after sign in.')">
                            <el-icon :size="12"><ArrowRight /></el-icon> Sell Coffee
                        </button>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-4 md:px-8 py-6">
                <div v-if="activeTab !== 'market'" class="rounded-2xl border border-slate-200 bg-white py-16 px-6 text-center text-slate-500 shadow-sm">
                    <p class="text-sm">The <span class="font-semibold text-slate-950">{{ tabs.find(t => t.key === activeTab)?.label }}</span> workspace opens once you're signed in to the Exchange terminal.</p>
                    <a href="#top" class="inline-block mt-3 text-sm font-semibold text-slate-950 hover:text-slate-700">Back to Market</a>
                </div>

                <template v-else>
                    <!-- Main layout -->
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                        <!-- Listings -->
                        <div class="xl:col-span-9">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-base font-bold text-slate-950">Coffee Available for Trade</h2>
                                    <span class="text-xs font-mono text-slate-500">Showing {{ filteredLots.length }} verified trade lots ({{ lots.length }} total on exchange)</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2 wp-filter-fields wp-sort-select">
                                        <span class="text-xs text-slate-500 whitespace-nowrap">Sort by:</span>
                                        <el-select v-model="sortBy" style="width: 170px;">
                                            <el-option v-for="o in sortOptions" :key="o" :label="o" :value="o" />
                                        </el-select>
                                    </div>
                                    <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5">
                                        <button type="button" class="p-1.5 px-2.5 text-xs font-medium rounded-md transition" :class="viewMode === 'grid' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'" @click="viewMode = 'grid'">
                                            <el-icon :size="13"><Grid /></el-icon>
                                        </button>
                                        <button type="button" class="p-1.5 px-2.5 text-xs font-medium rounded-md transition" :class="viewMode === 'list' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'" @click="viewMode = 'list'">
                                            <el-icon :size="13"><List /></el-icon>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Grid view -->
                            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div v-for="lot in filteredLots" :key="lot.id" class="wp-lot-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                                    <div class="wp-lot-card__art relative h-32 flex items-center justify-center">
                                        <el-icon :size="28" class="text-slate-300"><Coin /></el-icon>
                                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white text-slate-900 border border-slate-200 shadow-xs"><el-icon :size="9"><CircleCheckFilled /></el-icon> Verified</span>
                                            <span v-if="lot.eudr" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-white text-slate-600 border border-slate-200 shadow-xs"><el-icon :size="9"><LocationFilled /></el-icon> EUDR</span>
                                        </div>
                                        <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-950/80 text-white backdrop-blur-sm">{{ lot.cupScore }} pts</span>
                                    </div>
                                    <div class="p-3.5 flex-1 flex flex-col bg-white">
                                        <div class="flex items-center justify-between text-[11px] font-mono mb-1">
                                            <span class="text-slate-400">#{{ lot.id }}</span>
                                            <span class="text-slate-600 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded text-[10px]">{{ lot.incoterm }}</span>
                                        </div>
                                        <h3 class="text-xs font-bold text-slate-950 truncate">{{ lot.name }}</h3>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ lot.origin }} · {{ lot.grade }} · {{ lot.processing }}</div>
                                        <div class="mt-auto pt-3 border-t border-slate-100">
                                            <div class="flex items-baseline justify-between mb-2">
                                                <div>
                                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Available</span>
                                                    <span class="text-xs font-mono font-bold text-slate-900">{{ lot.qtyKg.toLocaleString() }} kg</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Spot Price</span>
                                                    <span class="text-sm font-mono font-extrabold text-slate-950">${{ lot.pricePerKg.toFixed(2) }} <span class="text-[10px] font-normal text-slate-500">/kg</span></span>
                                                </div>
                                            </div>
                                            <div class="text-[10px] text-slate-500 truncate mb-3 flex items-center gap-1">
                                                <el-icon :size="10" class="text-slate-400"><OfficeBuilding /></el-icon> {{ lot.seller }}
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" class="flex-1 py-1.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition" @click="openDossier(lot)">View</button>
                                                <button v-if="lot.actionType === 'auction'" type="button" class="flex-1 py-1.5 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition" @click="handleBid(lot)">Bid Live</button>
                                                <template v-else>
                                                    <button type="button" class="flex-1 py-1.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition" @click="openOffer(lot)">Offer</button>
                                                    <button type="button" class="flex-1 py-1.5 text-xs font-bold text-white bg-slate-950 hover:bg-slate-800 rounded-lg transition" @click="handleInstantBuy(lot)">Buy</button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- List view -->
                            <div v-else class="mb-6 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm overflow-x-auto">
                                <table class="w-full text-left text-xs border-collapse min-w-[760px]">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-mono text-[11px] uppercase tracking-wider">
                                            <th class="p-3.5 pl-4">Coffee & Lot #</th>
                                            <th class="p-3.5">Origin & Basin</th>
                                            <th class="p-3.5">Grade & Process</th>
                                            <th class="p-3.5 text-right">Available Qty</th>
                                            <th class="p-3.5 text-right">Spot Price</th>
                                            <th class="p-3.5">Seller</th>
                                            <th class="p-3.5 pr-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <tr v-for="lot in filteredLots" :key="lot.id" class="hover:bg-slate-50/70 transition">
                                            <td class="p-3.5 pl-4">
                                                <div class="font-bold text-slate-950">{{ lot.name }}</div>
                                                <div class="text-[11px] font-mono text-slate-400">#{{ lot.id }}</div>
                                            </td>
                                            <td class="p-3.5">
                                                <div class="text-slate-900 font-medium">{{ lot.origin }}</div>
                                                <div class="text-[11px] text-slate-500">{{ lot.region }}</div>
                                            </td>
                                            <td class="p-3.5">
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-[10px] border border-slate-200">{{ lot.grade }}</span>
                                                <div class="text-[11px] text-slate-500 mt-0.5">{{ lot.processing }}</div>
                                            </td>
                                            <td class="p-3.5 text-right font-mono">
                                                <div class="font-bold text-slate-900">{{ lot.qtyKg.toLocaleString() }} kg</div>
                                                <div class="text-[10px] text-slate-400">{{ (lot.qtyKg / 1000).toFixed(1) }} MT</div>
                                            </td>
                                            <td class="p-3.5 text-right font-mono">
                                                <div class="font-bold text-slate-950 text-sm">${{ lot.pricePerKg.toFixed(2) }}</div>
                                                <div class="text-[10px] text-slate-400">{{ lot.incoterm }}</div>
                                            </td>
                                            <td class="p-3.5">
                                                <div class="font-medium text-slate-900">{{ lot.seller }}</div>
                                                <div class="text-[10px] font-mono text-slate-400">{{ lot.sellerType }}</div>
                                            </td>
                                            <td class="p-3.5 pr-4 text-right">
                                                <div class="inline-flex rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                                                    <button type="button" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border-r border-slate-200" @click="openDossier(lot)">View</button>
                                                    <button v-if="lot.actionType === 'auction'" type="button" class="px-3 py-1 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700" @click="handleBid(lot)">Bid</button>
                                                    <template v-else>
                                                        <button type="button" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border-r border-slate-200" @click="openOffer(lot)">Offer</button>
                                                        <button type="button" class="px-3 py-1 text-xs font-bold text-white bg-slate-950 hover:bg-slate-800" @click="handleInstantBuy(lot)">Buy</button>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-200">
                                <div class="text-xs font-mono text-slate-500">Showing <strong class="text-slate-900">1 - {{ filteredLots.length }}</strong> of <strong class="text-slate-900">128</strong> lots</div>
                                <div class="flex items-center gap-1 font-mono text-xs">
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-400 bg-white disabled:opacity-50" disabled>‹</button>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded bg-slate-950 text-white font-bold">1</button>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">2</button>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">3</button>
                                    <span class="px-1 text-slate-400">...</span>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">16</button>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">›</button>
                                </div>
                            </div>
                        </div>

                        <!-- Order Execution Desk -->
                        <div class="xl:col-span-3 sticky top-24">
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div class="p-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-md bg-slate-950 text-white flex items-center justify-center font-bold text-[10px]">T</span>
                                        <h3 class="text-xs font-bold text-slate-950 uppercase tracking-wide">Execution Desk</h3>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded"><el-icon :size="10"><Lock /></el-icon> Stanbic Custody</span>
                                </div>

                                <div class="px-3.5 py-2.5 border-b border-slate-100 flex items-center justify-between font-mono text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Escrow Liquidity</span>
                                        <span class="font-bold text-slate-950">$284,720.00 <span class="text-[10px] font-normal text-slate-500">USD</span></span>
                                    </div>
                                    <button type="button" class="text-slate-900 hover:text-slate-700 font-bold text-[11px] underline underline-offset-2" @click="ElMessage.info('Escrow top-up is available after sign in.')">+ Deposit</button>
                                </div>

                                <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                                    <div class="grid grid-cols-3 gap-1 bg-slate-200/70 p-1 rounded-lg">
                                        <button
                                            v-for="mode in deskModes" :key="mode.key" type="button"
                                            class="py-1 px-1 text-[11px] rounded-md text-center transition"
                                            :class="deskMode === mode.key ? 'font-bold bg-slate-950 text-white shadow-xs' : 'font-semibold text-slate-600 hover:text-slate-950'"
                                            @click="setDeskMode(mode.key)"
                                        >{{ mode.label }}</button>
                                    </div>
                                </div>

                                <div class="p-3.5 border-b border-slate-100 bg-white">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded">#{{ selectedLot.id }}</span>
                                        <span class="text-xs font-extrabold font-mono text-slate-950">${{ deskPrice.toFixed(2) }} / kg</span>
                                    </div>
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ selectedLot.name }} ({{ selectedLot.grade }})</div>
                                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-500 mt-1">
                                        <span>Avail: <strong class="text-slate-900">{{ selectedLot.qtyKg.toLocaleString() }} kg</strong></span>
                                        <span>{{ selectedLot.incoterm }}</span>
                                    </div>
                                </div>

                                <div class="p-3.5 space-y-3.5 wp-filter-fields">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide">Order Volume</label>
                                            <span class="text-[10px] font-mono text-slate-400">≈ {{ bagCount }} jute bags (60kg)</span>
                                        </div>
                                        <el-input v-model.number="deskQty" type="number">
                                            <template #append>kg</template>
                                        </el-input>
                                        <div class="grid grid-cols-3 gap-1 mt-1.5">
                                            <button type="button" class="py-0.5 text-[10px] font-mono font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 transition" @click="setDeskQty(1000)">1,000</button>
                                            <button type="button" class="py-0.5 text-[10px] font-mono font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 transition" @click="setDeskQty(2500)">2,500</button>
                                            <button type="button" class="py-0.5 text-[10px] font-mono font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 transition" @click="setDeskQty(selectedLot.qtyKg)">Max</button>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide">{{ currentDeskMode.priceLabel }}</label>
                                            <span class="text-[10px] font-mono text-slate-400">USD Floor</span>
                                        </div>
                                        <el-input v-model.number="deskPrice" type="number" :step="0.01">
                                            <template #prepend>$</template>
                                            <template #append>/kg</template>
                                        </el-input>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1">Delivery Term & Custody</label>
                                        <el-select v-model="deskIncoterm" value-key="label" style="width: 100%;">
                                            <el-option v-for="opt in incotermOptions" :key="opt.label" :label="opt.label" :value="opt" />
                                        </el-select>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 font-mono text-xs space-y-1.5">
                                        <div class="flex justify-between text-slate-500"><span>Coffee Subtotal:</span><span class="text-slate-900 font-semibold">{{ usd(coffeeSubtotal) }}</span></div>
                                        <div class="flex justify-between text-slate-500"><span>Stanbic Escrow Fee (0.75%):</span><span class="text-slate-900">{{ usd(escrowFee) }}</span></div>
                                        <div class="flex justify-between text-slate-500"><span>Freight & Inspection:</span><span class="text-slate-900">{{ freightTotal === 0 ? 'Included (FOB)' : (freightTotal > 0 ? '+' : '-') + usd(Math.abs(freightTotal)) }}</span></div>
                                        <div class="pt-1.5 mt-1 border-t border-slate-200 flex justify-between font-bold text-slate-950 text-xs">
                                            <span>Total Settlement:</span><span class="font-bold text-slate-950">{{ usd(grandTotal) }}</span>
                                        </div>
                                    </div>

                                    <el-button class="wp-btn-primary w-full" @click="executeDeskOrder">
                                        <el-icon :size="14" class="mr-1"><Lock /></el-icon> {{ currentDeskMode.action }}
                                    </el-button>
                                    <div class="text-center font-mono text-[10px] text-slate-400">24-Hour Settlement Guarantee · Tier-1 Escrow</div>
                                </div>

                                <div class="border-t border-slate-200 bg-slate-50 p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500">Live Trade Monitor</span>
                                        <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-200 text-slate-800 font-bold">Active</span>
                                    </div>
                                    <div class="space-y-1.5 font-mono text-[11px]">
                                        <div v-for="row in marketMonitor" :key="row.code" class="flex items-center justify-between p-1.5 bg-white border border-slate-200 rounded-lg">
                                            <span class="truncate text-slate-700">#{{ row.code }} {{ row.label }}</span>
                                            <span v-if="row.tag" class="text-[10px] font-semibold text-slate-900 bg-slate-100 border border-slate-200 px-1 rounded">{{ row.tag }}: {{ row.value }}</span>
                                            <span v-else class="text-[10px] text-slate-500">Vessel Sailing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </main>
        </div>

        <!-- Lot dossier dialog -->
        <el-dialog v-model="dossierOpen" width="600px" class="wp-exchange-dialog">
            <template #header>
                <div>
                    <div class="text-sm font-bold text-slate-950">{{ dossierLot?.name }} <span v-if="dossierLot">({{ dossierLot.grade }})</span></div>
                    <div class="text-xs font-mono text-slate-500">#{{ dossierLot?.id }} · Verified Sourcing Record · Stanbic Escrow Backed</div>
                </div>
            </template>
            <div v-if="dossierLot" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5">
                    <div class="h-36 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center mb-2">
                        <el-icon :size="30" class="text-slate-300"><Coin /></el-icon>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-lg text-[11px] font-mono text-slate-600 border border-slate-200 space-y-1">
                        <div><strong class="text-slate-900">GPS Polygon:</strong> 0.3476° N, 32.5825° E</div>
                        <div><strong class="text-slate-900">Crop Season:</strong> 2025/2026 Main Crop</div>
                        <div><strong class="text-slate-900">Dry Mill:</strong> {{ dossierLot.seller }}</div>
                    </div>
                </div>
                <div class="md:col-span-7 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-800">UCDA Verified {{ dossierLot.grade }}</span>
                        <span class="text-base font-extrabold font-mono text-slate-950">${{ dossierLot.pricePerKg.toFixed(2) }}/kg</span>
                    </div>
                    <div class="text-xs space-y-1.5 border-y border-slate-100 py-2">
                        <div class="flex justify-between"><span class="text-slate-500">Origin Basin:</span><span class="font-bold text-slate-900">{{ dossierLot.region }}, {{ dossierLot.origin }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Grade / Process:</span><span class="font-semibold text-slate-900">{{ dossierLot.grade }} / {{ dossierLot.processing }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Moisture Content:</span><span class="font-mono text-slate-900">{{ dossierLot.moisture }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Available Volume:</span><span class="font-mono font-bold text-slate-900">{{ dossierLot.qtyKg.toLocaleString() }} kg ({{ (dossierLot.qtyKg/1000).toFixed(1) }} MT)</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Seller Entity:</span><span class="font-medium text-slate-900">{{ dossierLot.seller }}</span></div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px] font-mono text-slate-600 flex items-start gap-1.5">
                        <el-icon :size="13" class="mt-0.5 flex-shrink-0"><Lock /></el-icon>
                        Tier-1 Stanbic Escrow: funds released following independent SGS verification and ocean B/L issuance.
                    </div>
                </div>
            </div>
            <template #footer>
                <el-button @click="dossierOpen = false">Close</el-button>
                <el-button class="wp-btn-primary" @click="dossierOpen = false; ElMessage.info('Full product profile is available after sign in.')">Open Product Profile</el-button>
            </template>
        </el-dialog>

        <!-- Make offer dialog -->
        <el-dialog v-model="offerOpen" title="Make Counter Offer" width="420px" class="wp-exchange-dialog">
            <div v-if="offerLot">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mb-3">
                    <div class="text-xs font-bold text-slate-950">{{ offerLot.name }} ({{ offerLot.grade }})</div>
                    <div class="text-[11px] font-mono text-slate-500 mt-0.5">Asking: ${{ offerLot.pricePerKg.toFixed(2) }}/kg · Available: {{ offerLot.qtyKg.toLocaleString() }} kg</div>
                </div>
                <div class="wp-filter-fields space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Proposed Price (USD / kg)</label>
                        <el-input v-model.number="offerPrice" type="number" :step="0.01"><template #prepend>$</template></el-input>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity (kg)</label>
                        <el-input v-model.number="offerQty" type="number" />
                        <span class="text-[10px] font-mono text-slate-400 mt-1 block">Min export order: 1,000 kg</span>
                    </div>
                </div>
                <div class="p-2.5 mt-3 rounded-lg bg-slate-50 border border-slate-200 text-[11px] font-mono text-slate-600">
                    <el-icon :size="12" class="text-slate-900 mr-1"><Lock /></el-icon> Funds secured via Stanbic Escrow upon offer acceptance.
                </div>
            </div>
            <template #footer>
                <el-button @click="offerOpen = false">Cancel</el-button>
                <el-button class="wp-btn-primary" @click="submitOffer">Submit Binding Offer</el-button>
            </template>
        </el-dialog>

        <!-- Create RFQ dialog -->
        <el-dialog v-model="rfqOpen" title="Broadcast Sourcing RFQ" width="420px" class="wp-exchange-dialog">
            <p class="text-xs text-slate-500 mb-3">Post your commercial coffee requirements directly to verified dry mills and exporters.</p>
            <div class="wp-filter-fields space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Coffee Type & Screen</label>
                    <el-input model-value="Uganda Robusta Screen 18+" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Target Volume (MT)</label>
                        <el-input model-value="20" type="number" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Max Price ($/kg)</label>
                        <el-input model-value="4.10" type="number" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Port</label>
                    <el-input model-value="Port of Jebel Ali, Dubai" />
                </div>
            </div>
            <template #footer>
                <el-button class="wp-btn-primary w-full" @click="broadcastRfq">Broadcast RFQ to Network</el-button>
            </template>
        </el-dialog>

        <!-- AI copilot dialog -->
        <el-dialog v-model="aiOpen" width="440px" class="wp-exchange-dialog">
            <template #header>
                <div class="flex items-center gap-2">
                    <el-icon :size="17" class="text-slate-950"><StarFilled /></el-icon>
                    <span class="font-bold text-sm text-slate-950">Bean Origin AI Copilot</span>
                </div>
            </template>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2 mb-3">
                <p class="text-slate-800 font-medium m-0"><strong>AI Assistant:</strong> "Hello Kato, I am connected to the live physical order book. What are you looking to source today?"</p>
                <div class="p-2 bg-white border border-slate-200 rounded font-mono text-[11px] text-slate-700">Matched 3 active lots meeting: <em>Robusta, Screen 18, under $4.20/kg</em></div>
                <p class="text-[11px] font-mono text-slate-400 m-0">1. #LOT-000124 (5,000 kg @ $4.20) · 2. #LOT-000412 (12,000 kg @ $4.10) · 3. #LOT-000388 (20,000 kg @ $4.15)</p>
            </div>
            <div class="flex gap-2 wp-filter-fields">
                <el-input v-model="aiDraft" placeholder="e.g. Draft purchase bid on #LOT-000124..." />
                <el-button class="wp-btn-primary flex-shrink-0" @click="aiOpen = false; ElMessage.success('Redirecting to Agentic Commerce order execution session (dummy preview).')">Send</el-button>
            </div>
        </el-dialog>
    </OuterLayout>
</template>

<style scoped>
.wp-lot-card__art {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
.wp-lot-card {
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}
.wp-lot-card:hover {
    border-color: #94a3b8;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    transform: translateY(-2px);
}

/* Element Plus field overrides — slate palette to match the mockup exactly */
.wp-filter-fields :deep(.el-input__wrapper),
.wp-filter-fields :deep(.el-select__wrapper) {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 0 0 1px #e2e8f0 inset;
    padding: 1px 11px;
}
.wp-filter-fields :deep(.el-input__wrapper.is-focus),
.wp-filter-fields :deep(.el-select__wrapper.is-focused) {
    background: #ffffff;
    box-shadow: 0 0 0 1px #0f172a inset;
}
.wp-filter-fields :deep(.el-input__inner),
.wp-filter-fields :deep(.el-select__selected-item) {
    font-size: 12px;
    color: #0f172a;
}
.wp-filter-fields :deep(.el-input__inner::placeholder) {
    color: #94a3b8;
}
.wp-filter-fields :deep(.el-input-group__prepend),
.wp-filter-fields :deep(.el-input-group__append) {
    background: #f1f5f9;
    color: #64748b;
    font-size: 11px;
    box-shadow: 0 0 0 1px #e2e8f0 inset;
}
.wp-sort-select :deep(.el-select__wrapper) { padding: 1px 8px; min-height: 30px; }

:deep(.wp-btn-primary.el-button) {
    background: #020617;
    border-color: #020617;
    color: #ffffff;
    font-weight: 600;
    font-size: 13px;
    border-radius: 10px;
}
:deep(.wp-btn-primary.el-button:hover),
:deep(.wp-btn-primary.el-button:focus) {
    background: #1e293b;
    border-color: #1e293b;
    color: #ffffff;
}

:deep(.wp-exchange-dialog.el-dialog) {
    border-radius: 16px;
}
</style>
