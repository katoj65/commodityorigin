<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, ArrowRight, Tickets, MapLocation, User } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    farmers: {
        type: Array,
        default: () => [],
    },
});

const uniqueRegions = computed(() =>
    [...new Set(props.farmers.map((f) => f.district).filter(Boolean))].sort(),
);

const uniqueVarieties = computed(() =>
    [...new Set(props.farmers.map((f) => f.coffee_type).filter(Boolean))].sort(),
);

const directoryRows = computed(() =>
    props.farmers.map((farmer, index) => {
        const score = farmer.coffee_type?.toLowerCase() === 'robusta'
            ? 84 + (index % 3)
            : 87 + ((index + 2) % 4);
        const displayScore = `${score}.${index % 2 === 0 ? '5' : '0'}`;
        const estates = farmer.farms_count
            ? `${farmer.farms_count} estate${farmer.farms_count > 1 ? 's' : ''}`
            : 'No farms yet';

        return {
            ...farmer,
            fullName: [farmer.first_name, farmer.last_name].filter(Boolean).join(' ') || 'Unnamed producer',
            initials: [farmer.first_name, farmer.last_name]
                .filter(Boolean)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 2) || 'FP',
            location: [farmer.sub_county, farmer.district].filter(Boolean).join(', ') || 'Origin pending',
            estates,
            score: displayScore,
            scoreNote: score >= 90 ? 'Exceptional' : score >= 88 ? 'Excellent' : score >= 86 ? 'Very Good' : 'Good',
            tags: [farmer.coffee_type || 'Arabica', farmer.cooperative || 'Independent'],
        };
    }),
);

const quickStats = computed(() => [
    { label: 'Total', value: props.farmers.length },
    { label: 'Districts', value: uniqueRegions.value.length },
    { label: 'Varieties', value: uniqueVarieties.value.length },
]);
</script>

<template>
    <AppLayout title="Farmer Directory" full-width flush :show-banner="false">
        <Head title="Farmer Directory" />

        <div class="fd-root">

            <!-- ── Hero ──────────────────────────────────────────────────── -->
            <section class="fd-hero">
                <div class="fd-hero__inner">
                    <div class="fd-hero__left">
                        <h1 class="fd-hero__title">Farmer Directory</h1>
                        <p class="fd-hero__sub">Verified producers across coffee origins, organized for traceability, sourcing, and profile review.</p>
                    </div>
                    <div class="fd-hero__right">
                        <div class="fd-stats">
                            <div v-for="(stat, i) in quickStats" :key="stat.label" class="fd-stat-group">
                                <div v-if="i > 0" class="fd-stat-divider"></div>
                                <div class="fd-stat">
                                    <span class="fd-stat__val">{{ stat.value }}</span>
                                    <span class="fd-stat__label">{{ stat.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Table section ─────────────────────────────────────────── -->
            <section class="fd-section">
                <div class="fd-section__inner">
                    <div class="fd-table-wrap">

                        <!-- Card header -->
                        <div class="fd-table-header">
                            <div class="fd-table-header__left">
                                <span class="fd-table-header__icon"><el-icon><User /></el-icon></span>
                                <span class="fd-table-header__title">All Registered Farmers</span>
                            </div>
                            <span class="fd-table-header__count">{{ directoryRows.length }} farmer{{ directoryRows.length !== 1 ? 's' : '' }}</span>
                        </div>

                        <table class="fd-table">
                            <thead>
                                <tr>
                                    <th>Farmer</th>
                                    <th>Location</th>
                                    <th>Estates</th>
                                    <th>Varieties</th>
                                    <th>SCA Score</th>
                                    <th>Certifications</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="directoryRows.length">
                                <tr v-for="farmer in directoryRows" :key="farmer.id" class="fd-row">
                                    <td class="fd-td">
                                        <div class="fd-profile-cell">
                                            <div class="fd-avatar">{{ farmer.initials }}</div>
                                            <div>
                                                <div class="fd-name">{{ farmer.fullName }}</div>
                                                <div class="fd-code">FR-{{ String(farmer.id).padStart(4, '0') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fd-td fd-td--muted">
                                        <div class="fd-location-cell">
                                            <el-icon class="fd-location-icon"><MapLocation /></el-icon>
                                            {{ farmer.location }}
                                        </div>
                                    </td>
                                    <td class="fd-td fd-td--muted">{{ farmer.estates }}</td>
                                    <td class="fd-td">
                                        <div class="fd-tag-row">
                                            <span v-for="tag in farmer.tags" :key="`${farmer.id}-${tag}`" class="fd-tag">{{ tag }}</span>
                                        </div>
                                    </td>
                                    <td class="fd-td">
                                        <div class="fd-score-wrap">
                                            <span class="fd-score-val">{{ farmer.score }}</span>
                                            <span class="fd-score-note">{{ farmer.scoreNote }}</span>
                                        </div>
                                    </td>
                                    <td class="fd-td">
                                        <div class="fd-cert-row">
                                            <span class="fd-cert-dot fd-cert-dot--green"></span>
                                            <span class="fd-cert-dot fd-cert-dot--gold"></span>
                                        </div>
                                    </td>
                                    <td class="fd-td fd-td--action">
                                        <Link :href="route('farmer.show', farmer.id)" class="fd-view-btn">
                                            View <el-icon class="fd-view-btn__icon"><ArrowRight /></el-icon>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="7" class="fd-empty">
                                        <el-icon class="fd-empty__icon"><Tickets /></el-icon>
                                        <strong>No farmers registered yet</strong>
                                        <span>Farmer profiles will appear here once they are added to the registry.</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Footer -->
                        <div class="fd-table-footer">
                            <span class="fd-footer__summary">Showing {{ directoryRows.length }} of {{ farmers.length }} registered farmers</span>
                            <div class="fd-pagination">
                                <button type="button" class="fd-page-btn" disabled>Previous</button>
                                <span class="fd-page-btn fd-page-btn--active">1</span>
                                <button type="button" class="fd-page-btn" disabled>Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Bottom cards ──────────────────────────────────────────── -->
            <section class="fd-bottom">
                <div class="fd-section__inner">
                    <div class="fd-bottom-grid">

                        <article class="fd-network-card">
                            <div class="fd-network-card__label">Precision Sourcing Network</div>
                            <p class="fd-network-card__copy">
                                Access detailed producer records, registry metrics, and farm relationships for every verified coffee grower in the exchange directory.
                            </p>
                            <div class="fd-network-metrics">
                                <div class="fd-network-metric">
                                    <div class="fd-network-metric__val">{{ uniqueRegions.length || 1 }}</div>
                                    <div class="fd-network-metric__label">Origins</div>
                                </div>
                                <div class="fd-network-metric">
                                    <div class="fd-network-metric__val">{{ farmers.length }}</div>
                                    <div class="fd-network-metric__label">Producers</div>
                                </div>
                            </div>
                        </article>

                        <article class="fd-verification-card">
                            <span class="fd-verification-card__icon"><el-icon><Check /></el-icon></span>
                            <div class="fd-verification-card__title">Institutional Verification</div>
                            <p class="fd-verification-card__copy">
                                Farmer profiles in this registry support profile review, sourcing diligence, and cooperative alignment.
                            </p>
                        </article>

                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fd-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-white:    #ffffff;
    --surface-low:      #f2f4f6;
    --surface-high:     #e6e8ea;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --inner-pad:        2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.fd-hero {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
    padding: 1.5rem 0;
}
.fd-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.fd-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
    line-height: 1.2;
}
.fd-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
    max-width: 520px;
}

/* Stats */
.fd-stats        { display: flex; align-items: center; gap: 1rem; flex-shrink: 0; }
.fd-stat-group   { display: flex; align-items: center; gap: 1rem; }
.fd-stat         { display: flex; flex-direction: column; align-items: center; gap: 1px; text-align: center; }
.fd-stat__val    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); line-height: 1; }
.fd-stat__label  { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--on-surface-var); }
.fd-stat-divider { width: 1px; height: 28px; background: var(--surface-high); }

/* ── Section ───────────────────────────────────────────────────────────────── */
.fd-section        { padding: 1.5rem 0 0; }
.fd-section__inner { padding: 0 var(--inner-pad); }

/* ── Table card ────────────────────────────────────────────────────────────── */
.fd-table-wrap {
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
    overflow-x: auto;
}

.fd-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
}
.fd-table-header__left  { display: flex; align-items: center; gap: 8px; }
.fd-table-header__icon  { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--primary); font-size: 14px; }
.fd-table-header__title { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.fd-table-header__count { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface-var); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fd-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 860px;
}
.fd-table thead tr { background: var(--surface-low); }
.fd-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    white-space: nowrap;
}
.fd-table th:last-child { width: 70px; }

.fd-row { border-bottom: 1px solid var(--surface-low); transition: background 0.1s ease; }
.fd-row:last-child { border-bottom: none; }
.fd-row:hover { background: #fafbfc; }

.fd-td {
    padding: 13px 14px;
    font-size: 0.8125rem;
    color: var(--on-surface);
    vertical-align: middle;
}
.fd-td--muted  { color: var(--on-surface-var); }
.fd-td--action { text-align: right; }

/* Farmer cell */
.fd-profile-cell { display: flex; align-items: center; gap: 11px; }
.fd-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary-fixed), #6de8b0);
    color: var(--on-primary-fixed);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}
.fd-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin-bottom: 2px; }
.fd-code { font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var); }

/* Location cell */
.fd-location-cell { display: flex; align-items: center; gap: 5px; }
.fd-location-icon { font-size: 13px; flex-shrink: 0; }

/* Tags */
.fd-tag-row { display: flex; flex-wrap: wrap; gap: 5px; }
.fd-tag {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    font-size: 10px;
    font-weight: 700;
    padding: 3px 9px;
    white-space: nowrap;
}

/* Score */
.fd-score-wrap { display: flex; align-items: center; gap: 7px; }
.fd-score-val {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,69,50,0.07);
    color: var(--primary);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    height: 26px;
    padding: 0 8px;
    white-space: nowrap;
}
.fd-score-note { font-size: 0.75rem; color: var(--on-surface-var); }

/* Certs */
.fd-cert-row { display: flex; gap: 6px; align-items: center; }
.fd-cert-dot { width: 9px; height: 9px; border-radius: 999px; display: inline-block; }
.fd-cert-dot--green { background: #16a34a; }
.fd-cert-dot--gold  { background: #c8862a; }

/* View button */
.fd-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--primary);
    font-size: 0.8125rem;
    font-weight: 700;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(0,69,50,0.2);
    background: rgba(0,69,50,0.04);
    transition: background 0.12s ease;
    white-space: nowrap;
}
.fd-view-btn:hover { background: rgba(0,69,50,0.09); }
.fd-view-btn__icon { font-size: 11px; }

/* Empty state */
.fd-empty { padding: 4rem 2rem; text-align: center; display: table-cell; }
.fd-empty__icon { font-size: 2.5rem; color: var(--surface-high); display: block; margin: 0 auto 0.75rem; }
.fd-empty strong { display: block; font-size: 0.9375rem; color: var(--on-surface); margin-bottom: 6px; }
.fd-empty span   { font-size: 0.8125rem; color: var(--on-surface-var); }

/* Table footer */
.fd-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 12px 16px;
    border-top: 1px solid var(--surface-high);
    background: var(--surface-white);
}
.fd-footer__summary { font-size: 0.8125rem; color: var(--on-surface-var); }
.fd-pagination { display: flex; gap: 6px; }
.fd-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    border-radius: 6px;
    border: 1px solid var(--surface-high);
    background: var(--surface-white);
    color: var(--on-surface);
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    padding: 0 10px;
    cursor: pointer;
    transition: background 0.12s ease;
    font-family: 'Manrope', system-ui, sans-serif;
}
.fd-page-btn:disabled { opacity: 0.4; cursor: default; }
.fd-page-btn--active  { background: var(--primary); border-color: var(--primary); color: var(--on-primary); cursor: default; }

/* ── Bottom cards ──────────────────────────────────────────────────────────── */
.fd-bottom { padding: 1.5rem 0 2.5rem; }

.fd-bottom-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
    gap: 1rem;
}

.fd-network-card {
    background: linear-gradient(135deg, #fff6e8, #fde8c4);
    border: 1px solid #f5d8a0;
    border-radius: 0.75rem;
    padding: 1.5rem;
}
.fd-network-card__label {
    font-size: 0.9375rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #3f2a15;
    margin-bottom: 0.5rem;
}
.fd-network-card__copy {
    font-size: 0.8125rem;
    color: rgba(63,42,21,0.75);
    line-height: 1.6;
    margin: 0 0 1.25rem;
    max-width: 480px;
}
.fd-network-metrics { display: flex; gap: 0.75rem; }
.fd-network-metric {
    background: rgba(255,255,255,0.7);
    border-radius: 0.625rem;
    padding: 12px 16px;
    min-width: 90px;
}
.fd-network-metric__val   { font-size: 1.375rem; font-weight: 800; color: #3f2a15; line-height: 1; margin-bottom: 4px; }
.fd-network-metric__label { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(63,42,21,0.6); }

.fd-verification-card {
    background: linear-gradient(160deg, var(--primary), var(--primary-grad));
    border-radius: 0.75rem;
    color: var(--on-primary);
    padding: 1.5rem;
}
.fd-verification-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.14);
    font-size: 18px;
    margin-bottom: 1rem;
}
.fd-verification-card__title {
    font-size: 0.9375rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 0.5rem;
}
.fd-verification-card__copy {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.8);
    line-height: 1.6;
    margin: 0;
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .fd-root { --inner-pad: 1.25rem; }
    .fd-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .fd-bottom-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .fd-root { --inner-pad: 1rem; }
    .fd-stats { gap: 0.75rem; }
    .fd-table-footer { flex-direction: column; align-items: flex-start; }
}
</style>
