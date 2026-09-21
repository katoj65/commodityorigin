<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    OfficeBuilding, CircleCheck, CircleCheckFilled, Star, StarFilled, Share, Message,
    Box, Sell, RefreshRight, Document, LocationFilled, Van, Calendar,
    Odometer, UserFilled, EditPen, Delete, Plus, ChatDotRound,
    CoffeeCup, Tickets, Lock, Download, Medal, MapLocation, Ship,
    PieChart, Files, DocumentChecked, Promotion, ArrowRight, Grid,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import EditBusinessProfileDialog from '@/Components/Modals/EditBusinessProfileDialog.vue';
import AddBusinessMemberDialog from '@/Components/Modals/AddBusinessMemberDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    businessProfile: { type: Object, default: null },
    businessTypeOptions: { type: Array, default: () => [] },
    businessMembers: { type: Array, default: () => [] },
});

/* ── Ported from code.html / DESIGN.md ("Institutional-grade Clarity")
   onto this app's own --dp-* theme tokens and BEM (`bp-`) conventions —
   same porting approach used on LotProfile.vue / BatchProfile.vue. Every
   value that has a genuine BusinessProfile / BusinessMember column is
   real; sections with no corresponding schema (certifications, trade
   ledger, products/offers/RFQs, audit timeline, document vault, farm
   network stats, AI grade) stay illustrative, exactly as ported from the
   mockup — noted inline at each spot. ────────────────────────────────── */
const business = computed(() => props.businessProfile || {});
const hasBusinessProfile = computed(() => Boolean(props.businessProfile));

const businessName = computed(() => business.value.business_name || 'Business Profile');

const businessTypeLabel = computed(() => {
    if (!business.value.business_type) return '';
    return String(business.value.business_type)
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
});

const industryLine = computed(() => business.value.industry || businessTypeLabel.value || 'Coffee Trading Business');

const fullAddress = computed(() => [
    business.value.address_line_1, business.value.address_line_2,
    business.value.city, business.value.state, business.value.country, business.value.postal_code,
].filter(Boolean).join(', '));

const locationLine = computed(() => [business.value.city, business.value.country].filter(Boolean).join(', ') || 'Location not on file');

const memberSinceYear = computed(() => {
    if (!business.value.created_at) return null;
    const year = new Date(business.value.created_at).getFullYear();
    return Number.isNaN(year) ? null : year;
});

function initials(name) {
    return String(name || '').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase() || 'BO';
}

/* ── Edit / delete business profile ────────────────────────────────────── */
const editBusinessOpen = ref(false);
const deleteBusinessOpen = ref(false);
const deletingBusiness = ref(false);

function deleteBusinessProfile() {
    deletingBusiness.value = true;
    router.delete(route('profile.business.destroy'), {
        preserveScroll: true,
        onFinish: () => {
            deletingBusiness.value = false;
            deleteBusinessOpen.value = false;
        },
    });
}

/* ── Add / edit / remove business member ───────────────────────────────── */
const memberDialogOpen = ref(false);
const memberBeingEdited = ref(null);

function openAddMemberDialog() {
    memberBeingEdited.value = null;
    memberDialogOpen.value = true;
}

function openEditMemberDialog(member) {
    memberBeingEdited.value = member;
    memberDialogOpen.value = true;
}

const removeMemberOpen = ref(false);
const removingMemberId = ref(null);
const memberToRemove = ref(null);

function openRemoveMemberDialog(member) {
    memberToRemove.value = member;
    removeMemberOpen.value = true;
}

function removeMember() {
    if (!memberToRemove.value) return;
    removingMemberId.value = memberToRemove.value.id;
    router.delete(route('business.members.destroy', memberToRemove.value.id), {
        preserveScroll: true,
        onFinish: () => {
            removingMemberId.value = null;
            removeMemberOpen.value = false;
            memberToRemove.value = null;
        },
    });
}

const featuredMembers = computed(() => props.businessMembers.slice(0, 3));
const otherMembersCount = computed(() => Math.max(props.businessMembers.length - featuredMembers.value.length, 0));

/* ── Save / share — purely local UI state, no backend field for this. ─── */
const saved = ref(false);

/* No multi-capability field exists on BusinessProfile (business_type is a
   single value) — this whole grid is illustrative. */
const capabilities = [
    { icon: Ship, label: 'Exporter', sub: 'Direct Global Ocean/Air' },
    { icon: Odometer, label: 'Processor & Dry Mill', sub: 'Hulling, Gravity, Color Sort' },
    { icon: Grid, label: 'Aggregator', sub: 'Regional Cooperative Hubs' },
    { icon: CoffeeCup, label: 'Quality & Cupping Lab', sub: 'CQI Standardized Protocols' },
    { icon: Box, label: 'Warehouse Storage', sub: 'Industrial Depot' },
    { icon: Van, label: 'Logistics & Clearing', sub: 'Port Forwarding' },
];

/* No certifications/verification table exists yet — illustrative. */
const certBadges = [
    { icon: UserFilled, label: 'Business Verified' },
    { icon: Document, label: 'Tax Cleared' },
    { icon: Box, label: 'Depot Inspected' },
    { icon: Medal, label: 'License Active' },
];
const certifications = [
    { name: 'Export License', detail: 'Government export authority • Active', status: 'Active', tone: 'primary' },
    { name: 'Geolocation Compliance Audit', detail: 'Deforestation-free polygon mapping • Active', status: '100% Compliant', tone: 'primary' },
    { name: 'Fair Trade Certification', detail: 'Living wage premium certified', status: 'Certified', tone: 'secondary' },
    { name: 'Sustainable Sourcing / CoC', detail: 'Chain of custody audited', status: 'Certified', tone: 'primary' },
];

/* No products/offers/RFQ linkage exists on BusinessProfile yet —
   illustrative sample data, same pattern as Batch Profile's dummy
   fallbacks. */
const products = [
    { tag: 'Screen 18', price: '$4.20 / kg FOB', name: 'Fine Robusta Screen 18', sub: 'Robusta • Grade 1', metric: '92.4% Retention', volume: '1,250 kg', status: 'In Stock', statusTone: 'primary' },
    { tag: '86.5 CQI', price: '$5.10 / kg FOB', name: 'Highland AA Arabica', sub: 'Arabica • Washed', metric: 'Screen 17/18 Washed', volume: '2,400 kg', status: 'In Stock', statusTone: 'primary' },
    { tag: '85.0 CQI', price: '$4.85 / kg FOB', name: 'Natural Dry Micro-lot', sub: 'Arabica • Natural', metric: 'Grade 1 Sun-Dried', volume: '1,000 kg', status: 'Reserved 250kg', statusTone: 'secondary' },
];

const offers = [
    { code: '#OFF-0042', tag: 'Active Spot', tagTone: 'primary', title: '500 kg Robusta Screen 18', price: '$4.20 / kg', note: 'Spot Stock, Bonded Depot • Valid until 30 Sep 2026' },
    { code: '#OFF-0039', tag: 'Forward Contract', tagTone: 'secondary', title: '1,200 kg Washed Arabica AA', price: '$5.05 / kg', note: 'Harvest delivery window: November 2026' },
];

const rfqs = [
    { code: '#RFQ-2026-081', tag: 'Target: 25,000 kg', title: 'Raw Sun-Dried Robusta Kiboko / Parchment', note: 'Moisture < 13.0%, Screen 15+' },
    { code: '#RFQ-2026-074', tag: 'Target: 10,000 kg', title: 'Organic Certified Arabica Parchment', note: 'Grade A Certified Organic' },
];

/* No farm/collection linkage rollup exists for a business yet —
   illustrative. */
const farmStats = [
    { label: 'Verified Farms', value: '125', sub: 'Polygon Mapped' },
    { label: 'Partner Cooperatives', value: '4', sub: 'Regional Hubs' },
    { label: 'Sourcing Regions', value: '3', sub: 'Origin Basins' },
    { label: 'Collection Events', value: '12', sub: 'Active This Season' },
];
const cooperatives = ['Mukono Smallholder Agro-Coop', 'Rwenzori Highlands Union', 'Sipi Organic Producers', 'Bugisu Central Cooperative'];

/* No trade-execution ledger tied to a business exists yet —
   illustrative. */
const ledgerStats = [
    { label: 'Completed Trades', value: '36' },
    { label: 'On-Time SLA', value: '99.4%' },
    { label: 'Default Claims', value: '0' },
];
const destinations = [
    { name: 'UAE (Dubai)', count: '14 Shipments' },
    { name: 'Germany (Hamburg)', count: '11 Shipments' },
    { name: 'United Kingdom', count: '6 Shipments' },
    { name: 'United States', count: '5 Shipments' },
];

/* No audit-log table exists for a business profile yet — illustrative. */
const timeline = [
    { date: 'Sep 2026', tag: 'Bilateral Trade', text: 'Published bilateral spot offer for 500 kg Fine Robusta Screen 18.', tone: 'primary' },
    { date: 'Jun 2026', tag: 'Harvest Ingestion', text: 'Published 3 new certified micro-lots from partner cooperatives.', tone: 'primary' },
    { date: 'Apr 2026', tag: 'Ocean Fulfillment', text: 'Completed 50-tonne bulk export delivery (Contract Order #ORD-7712).', tone: 'secondary' },
    { date: 'Feb 2026', tag: 'Official Audit', text: 'Passed the 2026 milling & export facility compliance verification.', tone: 'muted' },
];

/* No document vault exists for a business profile yet — illustrative. */
const documents = [
    { icon: Document, name: 'Certificate of Incorporation & Business Registration', meta: 'PDF • Verified by Registrar' },
    { icon: CircleCheck, name: 'Exporter License (Current Year)', meta: 'PDF • Annual Government Mandate' },
    { icon: DocumentChecked, name: 'Standard Export Quality & Cupping Protocol', meta: 'PDF • CQI Standard Specification' },
    { icon: MapLocation, name: 'Geolocation Compliance Dossier', meta: 'PDF • Zero Deforestation' },
];
</script>

<template>
    <MainLayout :title="businessName">
        <Head :title="businessName" />

        <div class="bp-page">
            <div v-if="!hasBusinessProfile" class="bp-card bp-empty">
                <el-icon :size="22"><OfficeBuilding /></el-icon>
                <h2>No business profile on file yet</h2>
                <p>Complete your business details to unlock the full trading dossier.</p>
                <button type="button" class="bp-btn bp-btn--primary" @click="editBusinessOpen = true">
                    <el-icon :size="14"><Plus /></el-icon> Complete Business Profile
                </button>
            </div>

            <template v-else>
                <!-- ── 1. Header & Trust Cockpit ─────────────────────────────── -->
                <section class="bp-hero">
                    <div class="bp-hero__top">
                        <div class="bp-hero__identity">
                            <div class="bp-hero__logo">
                                <img v-if="business.logo_url" :src="business.logo_url" :alt="businessName">
                                <span v-else>{{ initials(businessName) }}</span>
                            </div>
                            <div class="bp-hero__text">
                                <div class="bp-hero__name-row">
                                    <h1 class="bp-hero__name">{{ businessName }}</h1>
                                    <span class="bp-pill bp-pill--verified">
                                        <el-icon :size="13"><CircleCheckFilled /></el-icon> Verified Organization
                                    </span>
                                </div>
                                <div class="bp-hero__meta">
                                    <span class="bp-hero__meta-item bp-hero__meta-item--accent"><el-icon :size="14"><Van /></el-icon> {{ industryLine }}</span>
                                    <span class="bp-dot">•</span>
                                    <span class="bp-hero__meta-item"><el-icon :size="14"><LocationFilled /></el-icon> {{ locationLine }}</span>
                                    <span class="bp-dot">•</span>
                                    <span class="bp-hero__meta-item"><el-icon :size="14"><Calendar /></el-icon> Member since {{ memberSinceYear || '—' }}</span>
                                    <span class="bp-dot">•</span>
                                    <span class="bp-hero__meta-item bp-hero__meta-item--accent"><el-icon :size="14"><Odometer /></el-icon> 99.4% On-time SLA</span>
                                </div>
                            </div>
                        </div>
                        <div class="bp-hero__top-actions">
                            <button type="button" class="bp-btn bp-btn--ghost" @click="saved = !saved">
                                <el-icon :size="16"><component :is="saved ? StarFilled : Star" /></el-icon>
                                <span>{{ saved ? 'Saved' : 'Save Contact' }}</span>
                            </button>
                            <button type="button" class="bp-btn bp-btn--ghost">
                                <el-icon :size="16"><Share /></el-icon><span>Share</span>
                            </button>
                            <button type="button" class="bp-btn bp-btn--ghost" @click="editBusinessOpen = true">
                                <el-icon :size="16"><EditPen /></el-icon><span>Edit</span>
                            </button>
                            <button type="button" class="bp-btn bp-btn--ghost bp-btn--ghost-danger" @click="deleteBusinessOpen = true">
                                <el-icon :size="16"><Delete /></el-icon><span>Delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="bp-hero__bar">
                        <div class="bp-hero__bar-actions">
                            <a v-if="business.contact_email" :href="`mailto:${business.contact_email}`" class="bp-btn bp-btn--primary">
                                <el-icon :size="16"><Message /></el-icon><span>Contact Business</span>
                            </a>
                            <button v-else type="button" class="bp-btn bp-btn--primary" disabled>
                                <el-icon :size="16"><Message /></el-icon><span>Contact Business</span>
                            </button>
                            <a class="bp-btn bp-btn--soft" href="#products-section">
                                <el-icon :size="16"><Box /></el-icon><span>View Products <strong>(3)</strong></span>
                            </a>
                            <a class="bp-btn bp-btn--soft" href="#offers-section">
                                <el-icon :size="16"><Sell /></el-icon><span>View Offers <strong>(2)</strong></span>
                            </a>
                            <a class="bp-btn bp-btn--soft" href="#ledger-section">
                                <el-icon :size="16"><RefreshRight /></el-icon><span>Completed Trades <strong>(36)</strong></span>
                            </a>
                        </div>
                        <span class="bp-hero__bar-chip"><span class="bp-dot-live"></span> Platform Verified</span>
                    </div>
                </section>

                <!-- ── Core split: Overview / Spec / Roles ───────────────────── -->
                <div class="bp-split">
                    <div class="bp-split__main">
                        <!-- 2. Active Operational Capabilities -->
                        <div class="bp-card">
                            <div class="bp-card-head">
                                <h2 class="bp-eyebrow">2. Active Operational Capabilities</h2>
                                <span class="bp-card-head__note">Verified by In-person Audit</span>
                            </div>
                            <div class="bp-cap-grid">
                                <div v-for="c in capabilities" :key="c.label" class="bp-cap-tile">
                                    <el-icon :size="18" class="bp-cap-tile__icon"><component :is="c.icon" /></el-icon>
                                    <div class="bp-cap-tile__text">
                                        <span class="bp-cap-tile__label">{{ c.label }}</span>
                                        <span class="bp-cap-tile__sub">{{ c.sub }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Official Registry & Corporate Specifications -->
                        <div class="bp-card">
                            <div class="bp-card-head">
                                <h2 class="bp-eyebrow">3. Official Registry &amp; Corporate Specifications</h2>
                            </div>
                            <div class="bp-spec-grid">
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Full Legal Entity Name</span>
                                    <span class="bp-spec-tile__value">{{ business.business_name || '—' }}</span>
                                </div>
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Registration / Tax ID</span>
                                    <span class="bp-spec-tile__value bp-mono bp-accent-text">{{ business.registration_number || business.tax_id || '—' }}</span>
                                </div>
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Business Operational Type</span>
                                    <span class="bp-spec-tile__value">{{ businessTypeLabel || '—' }}</span>
                                </div>
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Country / Jurisdiction</span>
                                    <span class="bp-spec-tile__value">{{ business.country || '—' }}</span>
                                </div>
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Registered Physical Address</span>
                                    <span class="bp-spec-tile__value">{{ fullAddress || '—' }}</span>
                                </div>
                                <div class="bp-spec-tile">
                                    <span class="bp-spec-tile__label">Employees / Est. Year</span>
                                    <span class="bp-spec-tile__value">{{ business.employee_count ?? '—' }} staff{{ business.year_established ? ` • Est. ${business.year_established}` : '' }}</span>
                                </div>
                                <div class="bp-spec-tile bp-spec-tile--span2">
                                    <span class="bp-spec-tile__label">Official Communications</span>
                                    <span class="bp-spec-tile__value">{{ [business.contact_email, business.contact_phone].filter(Boolean).join(' • ') || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bp-split__side">
                        <!-- 4. Relationship Cockpit -->
                        <div class="bp-card">
                            <div class="bp-card-head">
                                <h2 class="bp-eyebrow">4. Relationship Cockpit</h2>
                                <span class="bp-dot-live"></span>
                            </div>
                            <div class="bp-cockpit-status">
                                <span class="bp-cockpit-status__label">Connected Roaster Status</span>
                                <div class="bp-cockpit-status__value"><el-icon :size="16"><ChatDotRound /></el-icon> Saved Commercial Partner</div>
                                <p>2 Direct Inquiries · 1 Bilateral Trade settled</p>
                            </div>
                            <div class="bp-cockpit-actions">
                                <button type="button" class="bp-btn bp-btn--primary bp-btn--block"><el-icon :size="16"><ChatDotRound /></el-icon> Send Direct Message</button>
                                <button type="button" class="bp-btn bp-btn--soft-secondary bp-btn--block"><el-icon :size="16"><CoffeeCup /></el-icon> Request Sample / Cupping</button>
                                <button type="button" class="bp-btn bp-btn--outline bp-btn--block"><el-icon :size="16"><Tickets /></el-icon> Request Tailored Quotation</button>
                            </div>
                        </div>

                        <!-- 5. Regulatory & Certifications -->
                        <div class="bp-card">
                            <div class="bp-card-head">
                                <h2 class="bp-eyebrow">5. Regulatory &amp; Certifications</h2>
                                <span class="bp-card-head__note bp-accent-text">100% Passed</span>
                            </div>
                            <div class="bp-cert-badges">
                                <div v-for="b in certBadges" :key="b.label" class="bp-cert-badge">
                                    <el-icon :size="15"><component :is="b.icon" /></el-icon><span>{{ b.label }}</span>
                                </div>
                            </div>
                            <div class="bp-cert-list">
                                <div v-for="c in certifications" :key="c.name" class="bp-cert-row">
                                    <div class="bp-cert-row__head">
                                        <span>{{ c.name }}</span>
                                        <span class="bp-tag-solid" :class="`bp-tag-solid--${c.tone}`">{{ c.status }}</span>
                                    </div>
                                    <span class="bp-cert-row__detail">{{ c.detail }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Designated Key Personnel — real BusinessMember data -->
                        <div class="bp-card">
                            <div class="bp-card-head">
                                <h2 class="bp-eyebrow">6. Designated Key Personnel</h2>
                                <button type="button" class="bp-icon-btn" title="Add member" @click="openAddMemberDialog">
                                    <el-icon :size="15"><Plus /></el-icon>
                                </button>
                            </div>
                            <div v-if="featuredMembers.length" class="bp-people">
                                <div v-for="m in featuredMembers" :key="m.id" class="bp-person">
                                    <div class="bp-person__avatar">
                                        <img v-if="m.photo_url" :src="m.photo_url" :alt="m.name">
                                        <span v-else>{{ initials(m.name) }}</span>
                                    </div>
                                    <div class="bp-person__text">
                                        <span class="bp-person__name">{{ m.name }}</span>
                                        <span class="bp-person__title">{{ [m.designation, m.position].filter(Boolean).join(' · ') || '—' }}</span>
                                    </div>
                                    <div class="bp-person__actions">
                                        <a v-if="m.email" :href="`mailto:${m.email}`" class="bp-icon-btn bp-icon-btn--sm" title="Email"><el-icon :size="14"><Message /></el-icon></a>
                                        <button type="button" class="bp-icon-btn bp-icon-btn--sm" title="Edit member" @click="openEditMemberDialog(m)"><el-icon :size="14"><EditPen /></el-icon></button>
                                        <button type="button" class="bp-icon-btn bp-icon-btn--sm bp-icon-btn--danger" title="Remove member" @click="openRemoveMemberDialog(m)"><el-icon :size="14"><Delete /></el-icon></button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="bp-empty bp-empty--inline">
                                <el-icon :size="20"><UserFilled /></el-icon>
                                <p>No team members registered yet.</p>
                            </div>
                            <Link v-if="otherMembersCount > 0" :href="route('business.members.index')" class="bp-more-link">
                                +{{ otherMembersCount }} more team member{{ otherMembersCount === 1 ? '' : 's' }} — Manage Team
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ── 7. Associated Products Catalog ────────────────────────── -->
                <section id="products-section" class="bp-card">
                    <div class="bp-card-head">
                        <div>
                            <h2 class="bp-eyebrow">7. Associated Products Catalog</h2>
                            <h3 class="bp-section-title">Commercial Lots &amp; Standard Screen Profiles</h3>
                        </div>
                        <Link :href="route('market.index')" class="bp-link-arrow">View All Products <el-icon :size="15"><ArrowRight /></el-icon></Link>
                    </div>
                    <div class="bp-product-grid">
                        <div v-for="p in products" :key="p.name" class="bp-product-tile">
                            <div class="bp-product-tile__head">
                                <span class="bp-tag-solid">{{ p.tag }}</span>
                                <span class="bp-accent-text bp-strong">{{ p.price }}</span>
                            </div>
                            <h4>{{ p.name }}</h4>
                            <span class="bp-product-tile__sub">{{ p.sub }}</span>
                            <div class="bp-product-tile__rows">
                                <div><span>Quality Metric</span><strong>{{ p.metric }}</strong></div>
                                <div><span>Available Volume</span><strong>{{ p.volume }}</strong></div>
                                <div><span>Status</span><strong class="bp-status-dot" :class="`bp-status-dot--${p.statusTone}`">{{ p.status }}</strong></div>
                            </div>
                            <button type="button" class="bp-btn bp-btn--outline bp-btn--block bp-btn--sm">View Product</button>
                        </div>
                    </div>
                </section>

                <!-- ── 8. Commercial Offers & 9. Procurement RFQs ────────────── -->
                <div class="bp-pair">
                    <section id="offers-section" class="bp-card bp-card--split">
                        <div>
                            <div class="bp-card-head">
                                <div>
                                    <h2 class="bp-eyebrow">8. Commercial Offers</h2>
                                    <h3 class="bp-section-title">Active Spot &amp; Forward Offers</h3>
                                </div>
                                <span class="bp-count-chip">{{ offers.length }} Active</span>
                            </div>
                            <div class="bp-offer-list">
                                <div v-for="o in offers" :key="o.code" class="bp-offer-row">
                                    <div class="bp-offer-row__head">
                                        <span class="bp-mono bp-accent-text">{{ o.code }}</span>
                                        <span class="bp-tag-solid" :class="`bp-tag-solid--${o.tagTone}`">{{ o.tag }}</span>
                                    </div>
                                    <div class="bp-offer-row__mid">
                                        <span>{{ o.title }}</span>
                                        <strong class="bp-accent-text">{{ o.price }}</strong>
                                    </div>
                                    <p>{{ o.note }}</p>
                                    <button type="button" class="bp-btn bp-btn--outline bp-btn--sm bp-btn--end">View Offer</button>
                                </div>
                            </div>
                        </div>
                        <Link :href="route('market.index')" class="bp-btn bp-btn--soft bp-btn--block">View All Commercial Offers <el-icon :size="15"><ArrowRight /></el-icon></Link>
                    </section>

                    <section class="bp-card bp-card--split">
                        <div>
                            <div class="bp-card-head">
                                <div>
                                    <h2 class="bp-eyebrow">9. Procurement Requirements</h2>
                                    <h3 class="bp-section-title">Active Mill Sourcing RFQs</h3>
                                </div>
                                <span class="bp-count-chip bp-count-chip--secondary">{{ rfqs.length }} Open</span>
                            </div>
                            <div class="bp-offer-list">
                                <div v-for="r in rfqs" :key="r.code" class="bp-offer-row">
                                    <div class="bp-offer-row__head">
                                        <span class="bp-mono bp-muted">{{ r.code }}</span>
                                        <span class="bp-tag-solid">{{ r.tag }}</span>
                                    </div>
                                    <span class="bp-offer-row__title-only">{{ r.title }}</span>
                                    <p>{{ r.note }}</p>
                                    <button type="button" class="bp-btn bp-btn--outline bp-btn--sm bp-btn--end">View Sourcing Order</button>
                                </div>
                            </div>
                        </div>
                        <Link :href="route('rfq.index')" class="bp-btn bp-btn--soft bp-btn--block">Submit Supply Proposal <el-icon :size="15"><Promotion /></el-icon></Link>
                    </section>
                </div>

                <!-- ── 10. Farms & Supply Network Summary ─────────────────────── -->
                <section class="bp-card">
                    <div class="bp-card-head">
                        <div>
                            <h2 class="bp-eyebrow">10. Producer &amp; Supply Chain Footprint</h2>
                            <h3 class="bp-section-title">Aggregated Farm Network &amp; Cooperatives</h3>
                        </div>
                        <Link :href="route('farm.index')" class="bp-link-arrow">Explore Geotagged Farms <el-icon :size="15"><MapLocation /></el-icon></Link>
                    </div>
                    <div class="bp-metric-grid bp-metric-grid--4">
                        <div v-for="s in farmStats" :key="s.label" class="bp-metric-tile">
                            <span class="bp-metric-tile__label">{{ s.label }}</span>
                            <span class="bp-metric-tile__value bp-accent-text">{{ s.value }}</span>
                            <span class="bp-metric-tile__sub">{{ s.sub }}</span>
                        </div>
                    </div>
                    <div class="bp-chip-row">
                        <span class="bp-chip-row__label">Affiliated Cooperatives:</span>
                        <span v-for="c in cooperatives" :key="c" class="bp-chip">{{ c }}</span>
                    </div>
                </section>

                <!-- ── 11. Platform Ledger & 12. Audit Trail ──────────────────── -->
                <div id="ledger-section" class="bp-pair">
                    <section class="bp-card">
                        <div class="bp-card-head">
                            <div>
                                <h2 class="bp-eyebrow">11. Platform Ledger</h2>
                                <h3 class="bp-section-title">Execution &amp; Destination Markets</h3>
                            </div>
                            <el-icon :size="20" class="bp-accent-text"><CircleCheck /></el-icon>
                        </div>
                        <div class="bp-ledger-stats">
                            <div v-for="s in ledgerStats" :key="s.label" class="bp-ledger-stat">
                                <span>{{ s.value }}</span>
                                <small>{{ s.label }}</small>
                            </div>
                        </div>
                        <span class="bp-block-label">Verified Export Destinations</span>
                        <div class="bp-dest-grid">
                            <div v-for="d in destinations" :key="d.name" class="bp-dest-row">
                                <span>{{ d.name }}</span>
                                <span class="bp-mono bp-accent-text">{{ d.count }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="bp-card">
                        <div class="bp-card-head">
                            <div>
                                <h2 class="bp-eyebrow">12. Audit Trail</h2>
                                <h3 class="bp-section-title">Chronological Platform Milestones</h3>
                            </div>
                        </div>
                        <div class="bp-timeline">
                            <div v-for="t in timeline" :key="t.date + t.tag" class="bp-timeline__row">
                                <span class="bp-timeline__dot" :class="`bp-timeline__dot--${t.tone}`"></span>
                                <div class="bp-timeline__head">
                                    <span class="bp-mono bp-accent-text">{{ t.date }}</span>
                                    <span class="bp-chip bp-chip--sm">{{ t.tag }}</span>
                                </div>
                                <p>{{ t.text }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ── 13. Document Vault & 14. Facilities ────────────────────── -->
                <div class="bp-grid-7-5">
                    <section class="bp-card">
                        <div class="bp-card-head">
                            <div>
                                <h2 class="bp-eyebrow">13. Compliance Document Vault</h2>
                                <h3 class="bp-section-title">Audited Corporate &amp; Quality Certificates</h3>
                            </div>
                            <el-icon :size="20" class="bp-muted"><Lock /></el-icon>
                        </div>
                        <div class="bp-doc-list">
                            <div v-for="d in documents" :key="d.name" class="bp-doc-row">
                                <el-icon :size="20" class="bp-accent-text"><component :is="d.icon" /></el-icon>
                                <div class="bp-doc-row__text">
                                    <span>{{ d.name }}</span>
                                    <small>{{ d.meta }}</small>
                                </div>
                                <div class="bp-doc-row__actions">
                                    <button type="button" class="bp-btn bp-btn--soft bp-btn--xs"><el-icon :size="12"><Files /></el-icon> View</button>
                                    <button type="button" class="bp-btn bp-btn--primary bp-btn--xs"><el-icon :size="12"><Download /></el-icon> Download</button>
                                </div>
                            </div>
                        </div>
                        <p class="bp-footnote">Documents are digitally signed and verified against the national registry.</p>
                    </section>

                    <section class="bp-card">
                        <div class="bp-card-head">
                            <div>
                                <h2 class="bp-eyebrow">14. Facilities &amp; Hubs</h2>
                                <h3 class="bp-section-title">Physical Milling &amp; Logistics Hub</h3>
                            </div>
                            <el-icon :size="20" class="bp-accent-text"><LocationFilled /></el-icon>
                        </div>
                        <div class="bp-map">
                            <span class="bp-map__pin"><span class="bp-dot-live"></span> {{ locationLine }}</span>
                            <el-icon :size="34" class="bp-map__icon"><MapLocation /></el-icon>
                        </div>
                        <div class="bp-hq-rows">
                            <div class="bp-hq-row">
                                <span>HQ &amp; Primary Depot</span>
                                <strong>{{ fullAddress || 'No address on file yet.' }}</strong>
                            </div>
                            <div class="bp-hq-row">
                                <span>Processing Mill Hub</span>
                                <strong>Central Dry Mill Facility</strong>
                            </div>
                        </div>
                        <button type="button" class="bp-btn bp-btn--soft bp-btn--block"><el-icon :size="16"><PieChart /></el-icon> View Full Logistics Route</button>
                    </section>
                </div>
            </template>
        </div>

        <EditBusinessProfileDialog
            v-model="editBusinessOpen"
            :business="businessProfile"
            :business-type-options="businessTypeOptions"
        />

        <AddBusinessMemberDialog v-model="memberDialogOpen" :member="memberBeingEdited" />

        <ConfirmDialog
            v-model="deleteBusinessOpen"
            eyebrow="Business Profile"
            title="Delete Business Profile"
            message="Are you sure you want to delete your business profile? This removes all registered team members too, and cannot be undone."
            confirm-text="Delete Business Profile"
            :auto-close="false"
            :loading="deletingBusiness"
            @confirm="deleteBusinessProfile"
        />

        <ConfirmDialog
            v-model="removeMemberOpen"
            eyebrow="Business Leadership"
            title="Remove Member"
            :message="memberToRemove ? `Remove ${memberToRemove.name} from your business? This can't be undone.` : ''"
            confirm-text="Remove Member"
            loading-text="Removing…"
            :auto-close="false"
            :loading="removingMemberId === memberToRemove?.id"
            @confirm="removeMember"
        />
    </MainLayout>
</template>

<style scoped>
.bp-page {
    --card-border: var(--dp-outline-variant);
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: var(--dp-font-sans);
}

/* ── Shared card shell ─────────────────────────────────────────────────── */
.bp-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow);
    padding: 26px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.bp-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.bp-card-head__note { font-size: 11px; color: var(--dp-on-surface-variant); font-weight: 600; }
.bp-eyebrow { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: var(--dp-primary); margin: 0; }
.bp-section-title { font-size: 1.0625rem; font-weight: 800; color: var(--dp-on-surface); margin: 3px 0 0; letter-spacing: -0.01em; }
.bp-accent-text { color: var(--dp-primary); }
.bp-muted { color: var(--dp-on-surface-variant); }
.bp-strong { font-weight: 700; }
.bp-mono { font-family: var(--dp-font-mono); }
.bp-prose { font-size: .9375rem; line-height: 1.6; color: var(--dp-on-surface); margin: 0; }
.bp-footnote { font-size: 11px; color: var(--dp-on-surface-variant); margin: 0; }

.bp-empty {
    align-items: center;
    text-align: center;
    padding: 48px 20px;
    color: var(--dp-on-surface-variant);
}
.bp-empty h2 { font-size: 1.0625rem; color: var(--dp-on-surface); margin: 6px 0 2px; }
.bp-empty p { margin: 0 0 14px; font-size: .875rem; }
.bp-empty--inline { padding: 18px 0; gap: 6px; }
.bp-empty--inline p { margin: 0; font-size: .8125rem; }

/* ── Buttons & chips ──────────────────────────────────────────────────── */
.bp-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    height: 38px; padding: 0 16px; border: none; border-radius: 8px;
    font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none;
    transition: opacity .15s ease, background .15s ease;
    white-space: nowrap;
}
.bp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.bp-btn--primary:hover:not(:disabled) { opacity: .88; }
.bp-btn--primary:disabled { opacity: .5; cursor: default; }
.bp-btn--soft { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.bp-btn--soft:hover { background: var(--dp-surface-container-high); }
.bp-btn--soft-secondary { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.bp-btn--soft-secondary:hover { opacity: .88; }
.bp-btn--ghost { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); }
.bp-btn--ghost:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.bp-btn--ghost-danger:hover { background: var(--dp-error-container); color: var(--dp-error); }
.bp-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: inset 0 0 0 1px var(--card-border); }
.bp-btn--outline:hover { background: var(--dp-surface-container-low); }
.bp-btn--sm { height: 32px; padding: 0 12px; font-size: 11.5px; }
.bp-btn--xs { height: 26px; padding: 0 10px; font-size: 11px; gap: 4px; }
.bp-btn--block { width: 100%; }
.bp-btn--end { align-self: flex-end; }

.bp-icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 8px; border: none;
    background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant);
    cursor: pointer; text-decoration: none; transition: background .15s ease, color .15s ease;
}
.bp-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.bp-icon-btn--sm { width: 26px; height: 26px; }
.bp-icon-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }

.bp-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
}
.bp-pill--verified { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

.bp-tag-solid { padding: 3px 9px; border-radius: 999px; font-size: .6875rem; font-weight: 700; background: var(--dp-primary); color: var(--dp-on-primary); }
.bp-tag-solid--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

.bp-chip { padding: 6px 12px; border-radius: 8px; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 12px; font-weight: 500; }
.bp-chip--sm { padding: 2px 8px; font-size: 10px; font-weight: 600; color: var(--dp-on-surface-variant); }
.bp-count-chip { padding: 4px 10px; border-radius: 6px; background: var(--dp-surface-container-low); font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.bp-count-chip--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

.bp-dot { color: var(--dp-outline-variant); }
.bp-dot-live { width: 8px; height: 8px; border-radius: 999px; background: var(--dp-secondary); flex-shrink: 0; }
.bp-link-arrow { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--dp-primary); text-decoration: none; white-space: nowrap; }
.bp-link-arrow:hover { opacity: .8; }
.bp-more-link { display: block; text-align: center; font-size: 11.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none; padding-top: 4px; }
.bp-more-link:hover { opacity: .8; }

/* ── 1. Hero ──────────────────────────────────────────────────────────── */
.bp-hero { position: relative; overflow: hidden; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius, 6px); box-shadow: var(--dp-card-shadow); }
.bp-hero__top { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; padding: 28px 28px 24px; }
.bp-hero__identity { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.bp-hero__logo {
    width: 72px; height: 72px; border-radius: 12px; background: var(--dp-primary); color: var(--dp-on-primary);
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; flex-shrink: 0; overflow: hidden;
}
.bp-hero__logo img { width: 100%; height: 100%; object-fit: cover; }
.bp-hero__name-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.bp-hero__name { font-size: 1.625rem; font-weight: 800; letter-spacing: -0.01em; color: var(--dp-on-surface); margin: 0; }
.bp-hero__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; margin-top: 8px; font-size: 12.5px; color: var(--dp-on-surface-variant); }
.bp-hero__meta-item { display: inline-flex; align-items: center; gap: 4px; }
.bp-hero__meta-item--accent { color: var(--dp-primary); font-weight: 700; }
.bp-hero__top-actions { display: flex; gap: 8px; flex-shrink: 0; }

.bp-hero__bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 18px 28px; background: var(--dp-surface-container-low); }
.bp-hero__bar-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.bp-hero__bar-chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 8px; background: var(--dp-surface-container-highest); font-size: 11.5px; font-weight: 600; color: var(--dp-on-surface-variant); }

/* ── Core split ───────────────────────────────────────────────────────── */
.bp-split { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; align-items: start; }
.bp-split__main, .bp-split__side { display: flex; flex-direction: column; gap: 24px; min-width: 0; }

/* ── Metric / spec / capability grids ────────────────────────────────── */
.bp-metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.bp-metric-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.bp-metric-tile { display: flex; flex-direction: column; gap: 3px; padding: 14px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-metric-tile__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-metric-tile__value { font-size: 1.125rem; font-weight: 800; color: var(--dp-on-surface); }
.bp-metric-tile__sub { font-size: 10.5px; color: var(--dp-primary); font-weight: 600; }

.bp-cap-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.bp-cap-tile { display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-cap-tile__icon { color: var(--dp-primary); flex-shrink: 0; }
.bp-cap-tile__text { display: flex; flex-direction: column; }
.bp-cap-tile__label { font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.bp-cap-tile__sub { font-size: 10px; color: var(--dp-on-surface-variant); }

.bp-spec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.bp-spec-tile { display: flex; flex-direction: column; gap: 3px; padding: 12px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-spec-tile--span2 { grid-column: span 2; }
.bp-spec-tile__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-spec-tile__value { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); }

/* ── Relationship cockpit ─────────────────────────────────────────────── */
.bp-cockpit-status { display: flex; flex-direction: column; gap: 4px; padding: 13px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-cockpit-status__label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-cockpit-status__value { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.bp-cockpit-status p { margin: 2px 0 0; font-size: 11px; color: var(--dp-on-surface-variant); }
.bp-cockpit-actions { display: flex; flex-direction: column; gap: 8px; }

/* ── Certification badges/list ───────────────────────────────────────── */
.bp-cert-badges { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.bp-cert-badge { display: flex; align-items: center; gap: 7px; padding: 9px; border-radius: 8px; background: var(--dp-surface-container-low); font-size: 11px; font-weight: 600; color: var(--dp-on-surface); }
.bp-cert-badge .el-icon { color: var(--dp-primary); }
.bp-cert-list { display: flex; flex-direction: column; gap: 10px; }
.bp-cert-row { padding: 11px; border-radius: 8px; background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 3px; }
.bp-cert-row__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.bp-cert-row__detail { font-size: 10.5px; color: var(--dp-on-surface-variant); }

/* ── Personnel ────────────────────────────────────────────────────────── */
.bp-people { display: flex; flex-direction: column; gap: 10px; }
.bp-person { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-person__avatar {
    width: 34px; height: 34px; border-radius: 999px; background: var(--dp-primary-container); color: var(--dp-on-primary-container);
    display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0; overflow: hidden;
}
.bp-person__avatar img { width: 100%; height: 100%; object-fit: cover; }
.bp-person__text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.bp-person__name { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bp-person__title { font-size: 10.5px; color: var(--dp-on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bp-person__actions { display: flex; gap: 4px; flex-shrink: 0; }

/* ── Products ─────────────────────────────────────────────────────────── */
.bp-product-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.bp-product-tile { display: flex; flex-direction: column; gap: 10px; padding: 16px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-product-tile__head { display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; }
.bp-product-tile h4 { font-size: .9375rem; font-weight: 800; color: var(--dp-on-surface); margin: 0; }
.bp-product-tile__sub { font-size: 11px; color: var(--dp-on-surface-variant); margin-top: -6px; }
.bp-product-tile__rows { display: flex; flex-direction: column; gap: 5px; font-size: 11.5px; }
.bp-product-tile__rows div { display: flex; justify-content: space-between; color: var(--dp-on-surface-variant); }
.bp-product-tile__rows strong { color: var(--dp-on-surface); font-weight: 700; }
.bp-status-dot { display: inline-flex; align-items: center; gap: 5px; }
.bp-status-dot::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); }
.bp-status-dot--secondary::before { background: var(--dp-secondary); }

/* ── Offers / RFQs ────────────────────────────────────────────────────── */
.bp-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.bp-pair > .bp-card { height: 100%; }
.bp-card--split { justify-content: space-between; }
.bp-offer-list { display: flex; flex-direction: column; gap: 10px; margin-top: 16px; }
.bp-offer-row { display: flex; flex-direction: column; gap: 6px; padding: 13px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-offer-row__head { display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; }
.bp-offer-row__mid { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.bp-offer-row__title-only { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.bp-offer-row p { margin: 0; font-size: 11px; color: var(--dp-on-surface-variant); }

/* ── Farm network ─────────────────────────────────────────────────────── */
.bp-chip-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.bp-chip-row__label { font-size: 11.5px; font-weight: 600; color: var(--dp-on-surface-variant); }

/* ── Platform ledger ──────────────────────────────────────────────────── */
.bp-ledger-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.bp-ledger-stat { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 13px; border-radius: 8px; background: var(--dp-surface-container-low); text-align: center; }
.bp-ledger-stat span { font-size: 1.25rem; font-weight: 800; color: var(--dp-on-surface); }
.bp-ledger-stat small { font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-block-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-dest-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.bp-dest-row { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; background: var(--dp-surface-container-low); font-size: 12px; }

/* ── Audit trail ──────────────────────────────────────────────────────── */
.bp-timeline { position: relative; display: flex; flex-direction: column; gap: 18px; padding-left: 20px; }
.bp-timeline::before { content: ''; position: absolute; left: 4px; top: 4px; bottom: 4px; width: 2px; background: var(--dp-outline-variant); }
.bp-timeline__row { position: relative; display: flex; flex-direction: column; gap: 3px; }
.bp-timeline__dot { position: absolute; left: -20px; top: 2px; width: 10px; height: 10px; border-radius: 999px; background: var(--dp-primary); box-shadow: 0 0 0 3px var(--dp-surface-container-lowest); }
.bp-timeline__dot--secondary { background: var(--dp-secondary); }
.bp-timeline__dot--muted { background: var(--dp-outline-variant); }
.bp-timeline__head { display: flex; align-items: center; gap: 8px; font-size: 11.5px; }
.bp-timeline__row p { margin: 0; font-size: 12px; font-weight: 600; color: var(--dp-on-surface); }

/* ── Document vault & facilities ──────────────────────────────────────── */
.bp-grid-7-5 { display: grid; grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); gap: 20px; align-items: start; }
.bp-doc-list { display: flex; flex-direction: column; gap: 8px; }
.bp-doc-row { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-doc-row__text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.bp-doc-row__text span { font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.bp-doc-row__text small { font-size: 10.5px; color: var(--dp-on-surface-variant); }
.bp-doc-row__actions { display: flex; gap: 6px; flex-shrink: 0; }

.bp-map { position: relative; height: 150px; border-radius: 8px; background: var(--dp-surface-container-high); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.bp-map__pin { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; background: color-mix(in srgb, var(--dp-surface-container-lowest) 90%, transparent); font-size: 11px; font-weight: 700; color: var(--dp-on-surface); }
.bp-map__icon { color: var(--dp-outline); opacity: .5; }
.bp-hq-rows { display: flex; flex-direction: column; gap: 8px; }
.bp-hq-row { display: flex; flex-direction: column; gap: 2px; padding: 11px; border-radius: 8px; background: var(--dp-surface-container-low); }
.bp-hq-row span { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.bp-hq-row strong { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .bp-split { grid-template-columns: 1fr; }
    .bp-grid-7-5 { grid-template-columns: 1fr; }
}
@media (max-width: 1024px) {
    .bp-metric-grid, .bp-metric-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bp-cap-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bp-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bp-pair { grid-template-columns: 1fr; }
    .bp-dest-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .bp-hero__top { flex-direction: column; align-items: flex-start; }
    .bp-metric-grid, .bp-metric-grid--4, .bp-cap-grid, .bp-product-grid, .bp-spec-grid, .bp-cert-badges { grid-template-columns: 1fr; }
    .bp-spec-tile--span2 { grid-column: span 1; }
}
</style>
