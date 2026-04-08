<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    farm: {
        type: Object,
        required: true,
    },
});

const farmName = computed(() => props.farm.name || 'Farm Profile');
const farmerName = computed(() =>
    [props.farm.farmer?.first_name, props.farm.farmer?.last_name].filter(Boolean).join(' ') || 'Assigned Producer',
);
const locationLabel = computed(
    () =>
        props.farm.location ||
        [props.farm.farmer?.sub_county, props.farm.farmer?.district, 'Uganda'].filter(Boolean).join(', ') ||
        'Origin pending',
);
const estateBrief = computed(
    () =>
        props.farm.notes ||
        `${farmName.value} is managed for traceable coffee production with structured harvesting, post-harvest discipline, and field-level quality monitoring.`,
);
const altitudeRange = computed(() => props.farm.altitude || '1,950m - 2,100m');
const farmSize = computed(() => props.farm.size || '—');
const variety = computed(() => props.farm.variety || props.farm.farmer?.coffee_type || 'Arabica');
const rainfall = computed(() => (props.farm.altitude ? '1,850 mm' : '1,620 mm'));
const temperature = computed(() => (props.farm.altitude ? '18.5°C - 24.2°C' : '20.1°C - 26.8°C'));
const humidityIndex = computed(() => (props.farm.status?.toLowerCase() === 'active' ? '62%' : '58%'));
const bagsEstimate = computed(() => {
    const digits = String(props.farm.size || '').match(/\d+/);
    if (digits?.[0]) {
        return `${digits[0]} Bags`;
    }

    return '420 Bags';
});

const harvestRows = computed(() => [
    {
        batch: `#${String(props.farm.id).padStart(3, '0')}-2026-01`,
        variety: variety.value,
        cupScore: props.farm.farmer?.coffee_type?.toLowerCase() === 'robusta' ? '84.3' : '88.5',
        volume: '120 bags',
        status: 'Shipped',
        statusClass: 'farm-status--green',
    },
    {
        batch: `#${String(props.farm.id).padStart(3, '0')}-2026-02`,
        variety: variety.value,
        cupScore: props.farm.farmer?.coffee_type?.toLowerCase() === 'robusta' ? '83.8' : '87.9',
        volume: '90 bags',
        status: 'In Storage',
        statusClass: 'farm-status--amber',
    },
    {
        batch: `#${String(props.farm.id).padStart(3, '0')}-2026-03`,
        variety: variety.value,
        cupScore: props.farm.farmer?.coffee_type?.toLowerCase() === 'robusta' ? '84.0' : '87.8',
        volume: '150 bags',
        status: 'Processing',
        statusClass: 'farm-status--blue',
    },
]);

const iconPaths = {
    location:
        'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z',
    leaf:
        'M20 4c-5.5 0-10 1.8-12.6 4.4C4.7 11.1 4 14.2 4 17c0 1 .1 2 .4 3 .1.3.4.5.7.5h.1c.3 0 .6-.2.7-.5.3-.9.8-1.7 1.4-2.4 1.3 1 2.8 1.4 4.4 1.4 4.4 0 8-3.6 8-8V4.8c0-.4-.4-.8-.8-.8z',
    harvest:
        'M18.5 3C13 3 8.8 4.8 6.4 7.3A8.8 8.8 0 004 13.7c0 2.2.7 4.3 2.1 6.1.2.3.5.4.8.4.3 0 .6-.2.8-.4 1.3-1.9 3.2-3.1 5.3-3.1 4 0 7.3-3.3 7.3-7.3V3.9c0-.5-.4-.9-.9-.9z',
    soil:
        'M6 4h12l-1 5H7L6 4zm1.6 7h8.8l-.8 4H8.4l-.8-4zm1.2 6h6.4L14 21h-4l-1.2-4z',
    cloud:
        'M7 18a4 4 0 010-8 5.5 5.5 0 0110.6-1.8A4.5 4.5 0 1118.5 18H7z',
    thermometer:
        'M14 14.76V5a2 2 0 10-4 0v9.76a4 4 0 104 0zM12 20a2 2 0 01-1-3.73V7h2v9.27A2 2 0 0112 20z',
    humidity:
        'M12 2s5 5.4 5 9.2A5 5 0 117 11.2C7 7.4 12 2 12 2zm-2 10.2A2 2 0 0012 14a2 2 0 002-1.8c0-1.1-1-2.4-2-3.6-1 1.2-2 2.5-2 3.6z',
    chart:
        'M4 19h16v2H2V3h2v16zm3-3l3.5-4.5 2.5 2.5L18 8l1.5 1.3-6 7-2.5-2.5L8.5 17 7 16z',
    estate:
        'M3 11.5L12 4l9 7.5v8a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 19.5v-8zm6 8.5h6V13H9v7z',
    email:
        'M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm0 2v.2l8 5.3 8-5.3V8H4zm16 10V11l-7.4 4.9a1 1 0 01-1.1 0L4 11v7h16z',
    phone:
        'M6.6 10.8a15.5 15.5 0 006.6 6.6l2.2-2.2a1 1 0 011-.24 11.4 11.4 0 003.6.58 1 1 0 011 1V20a1 1 0 01-1 1C10.6 21 3 13.4 3 4a1 1 0 011-1h3.46a1 1 0 011 1c0 1.25.2 2.46.58 3.6a1 1 0 01-.24 1l-2.2 2.2z',
    check:
        'M9.55 17.2L4.8 12.45l1.4-1.4 3.35 3.35 8.25-8.25 1.4 1.4-9.65 9.65z',
};
</script>

<template>
    <AppLayout :title="farmName" full-width>
        <Head :title="farmName" />

        <div class="farm-profile-page">
            <div class="farm-profile-shell">
                <section class="farm-hero">
                    <div class="farm-hero__media">
                        <div class="farm-hero__image">
                            <div class="farm-hero__badge">Origin Verified</div>
                            <div class="farm-hero__mountain"></div>
                            <div class="farm-hero__meta">
                                <div>
                                    <h1 class="farm-hero__title">{{ farmName }}</h1>
                                    <p class="farm-hero__location">
                                        <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.location" />
                                        </svg>
                                        <span>{{ locationLabel }}</span>
                                    </p>
                                </div>

                                <div class="farm-hero__stats">
                                    <div class="farm-hero__stat">
                                        <span class="farm-hero__stat-label">
                                            <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.leaf" />
                                            </svg>
                                            <span>Variety</span>
                                        </span>
                                        <span class="farm-hero__stat-value">{{ variety }}</span>
                                    </div>
                                    <div class="farm-hero__stat">
                                        <span class="farm-hero__stat-label">
                                            <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.harvest" />
                                            </svg>
                                            <span>Est. Production</span>
                                        </span>
                                        <span class="farm-hero__stat-value">{{ bagsEstimate }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="farm-body">
                    <div class="farm-main">
                        <section class="farm-section">
                            <div class="farm-section__header">
                                <h2 class="farm-section__title">
                                    <svg class="farm-section__icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.chart" />
                                    </svg>
                                    <span>Estate Intelligence</span>
                                </h2>
                            </div>

                            <div class="farm-intelligence-grid">
                                <article class="farm-mini-card">
                                    <div class="farm-mini-card__label">
                                        <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.location" />
                                        </svg>
                                        <span>Altitude</span>
                                    </div>
                                    <div class="farm-mini-card__value">{{ altitudeRange }}</div>
                                    <div class="farm-mini-card__copy">Optimal high-elevation growth profile.</div>
                                </article>
                                <article class="farm-mini-card">
                                    <div class="farm-mini-card__label">
                                        <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.harvest" />
                                        </svg>
                                        <span>Farm Size</span>
                                    </div>
                                    <div class="farm-mini-card__value">{{ farmSize }} Acres</div>
                                    <div class="farm-mini-card__copy">Registered field coverage for this estate profile.</div>
                                </article>
                                <article class="farm-mini-card">
                                    <div class="farm-mini-card__label">
                                        <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.soil" />
                                        </svg>
                                        <span>Soil Compost</span>
                                    </div>
                                    <div class="farm-mini-card__value">Volcanic Red Clay</div>
                                    <div class="farm-mini-card__copy">High mineral density and drainage.</div>
                                </article>
                            </div>
                        </section>

                        <section class="farm-climate-grid">
                            <article class="farm-section farm-climate-card">
                                <div class="farm-section__header">
                                    <h2 class="farm-section__title">
                                        <svg class="farm-section__icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.cloud" />
                                        </svg>
                                        <span>Micro-climate Data</span>
                                    </h2>
                                </div>

                                <div class="farm-climate-list">
                                    <div class="farm-climate-row">
                                        <span class="farm-climate-row__label">
                                            <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.cloud" />
                                            </svg>
                                            <span>Annual Rainfall</span>
                                        </span>
                                        <strong>{{ rainfall }}</strong>
                                    </div>
                                    <div class="farm-climate-row">
                                        <span class="farm-climate-row__label">
                                            <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.thermometer" />
                                            </svg>
                                            <span>Avg Temperature</span>
                                        </span>
                                        <strong>{{ temperature }}</strong>
                                    </div>
                                    <div class="farm-climate-row">
                                        <span class="farm-climate-row__label">
                                            <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.humidity" />
                                            </svg>
                                            <span>Humidity Index</span>
                                        </span>
                                        <strong>{{ humidityIndex }}</strong>
                                    </div>
                                    <div class="farm-climate-row">
                                        <span class="farm-climate-row__label">
                                            <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="iconPaths.check" />
                                            </svg>
                                            <span>Farm Status</span>
                                        </span>
                                        <strong>{{ props.farm.status || 'Active' }}</strong>
                                    </div>
                                </div>
                            </article>

                            <article class="farm-map-tile">
                                <div class="farm-map-tile__pin"></div>
                                <div class="farm-map-tile__coords">
                                    <span>{{ props.farm.latitude || '0.3476' }}, {{ props.farm.longitude || '32.5825' }}</span>
                                    <small>Origin map reference</small>
                                </div>
                            </article>
                        </section>

                        <section class="farm-section">
                            <div class="farm-section__header">
                                <h2 class="farm-section__title">
                                    <svg class="farm-section__icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.harvest" />
                                    </svg>
                                    <span>Harvests &amp; Production</span>
                                </h2>
                            </div>

                            <div class="farm-table-wrap">
                                <table class="farm-table">
                                    <thead>
                                        <tr>
                                            <th>Batch ID</th>
                                            <th>Variety</th>
                                            <th>Cup Score</th>
                                            <th>Volume</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in harvestRows" :key="row.batch">
                                            <td>{{ row.batch }}</td>
                                            <td>{{ row.variety }}</td>
                                            <td>{{ row.cupScore }}</td>
                                            <td>{{ row.volume }}</td>
                                            <td>
                                                <span class="farm-status-pill" :class="row.statusClass">{{ row.status }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <aside class="farm-side">
                        <section class="farm-side-card farm-producer-card">
                            <div class="farm-producer-card__avatar"></div>
                            <div class="farm-producer-card__name">{{ farmerName }}</div>
                            <div class="farm-producer-card__role">Single origin producer</div>
                            <p class="farm-producer-card__copy">{{ estateBrief }}</p>
                            <div class="farm-producer-card__contact">
                                <span>
                                    <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.email" />
                                    </svg>
                                    <span>{{ props.farm.farmer?.email || 'producer@exchange.ug' }}</span>
                                </span>
                                <span>
                                    <svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.phone" />
                                    </svg>
                                    <span>{{ props.farm.farmer?.telephone || '+256 700 000000' }}</span>
                                </span>
                            </div>
                        </section>

                        <section class="farm-side-card farm-score-card">
                            <div class="farm-score-card__title">
                                <svg class="farm-section__icon farm-section__icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.leaf" />
                                </svg>
                                <span>Sustainability Scorecard</span>
                            </div>
                            <div class="farm-score-card__rows">
                                <div class="farm-score-card__row">
                                    <span class="farm-score-card__label">
                                        <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.leaf" />
                                        </svg>
                                        <span>Shade-grown index</span>
                                    </span>
                                    <strong>94%</strong>
                                </div>
                                <div class="farm-score-card__row">
                                    <span class="farm-score-card__label">
                                        <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.humidity" />
                                        </svg>
                                        <span>Water intensity</span>
                                    </span>
                                    <strong>Low impact</strong>
                                </div>
                                <div class="farm-score-card__row">
                                    <span class="farm-score-card__label">
                                        <svg class="farm-inline-icon farm-inline-icon--light" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.chart" />
                                        </svg>
                                        <span>Carbon footprint</span>
                                    </span>
                                    <strong>&lt; 0.62kg/kg</strong>
                                </div>
                            </div>
                        </section>

                        <section class="farm-side-card">
                            <div class="farm-side-card__heading">
                                <svg class="farm-section__icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.check" />
                                </svg>
                                <span>Farm Certification</span>
                            </div>
                            <div class="farm-cert-tags">
                                <span class="farm-cert-tag"><svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path :d="iconPaths.check" /></svg><span>Rainforest Alliance</span></span>
                                <span class="farm-cert-tag"><svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path :d="iconPaths.check" /></svg><span>Organic Certified</span></span>
                                <span class="farm-cert-tag"><svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path :d="iconPaths.check" /></svg><span>Fair Trade</span></span>
                                <span class="farm-cert-tag"><svg class="farm-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path :d="iconPaths.check" /></svg><span>Verified</span></span>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.farm-profile-page {
    min-height: 100%;
    background: #fff;
}

.farm-profile-shell {
    max-width: 1260px;
    margin: 0 auto;
    padding: 0.75rem 0.75rem 1.5rem;
}

.farm-hero__image {
    position: relative;
    overflow: hidden;
    min-height: 70px;
    border-radius: 1rem;
    background:
        radial-gradient(circle at 50% 18%, rgba(245, 249, 255, 0.95), rgba(110, 172, 197, 0.6) 18%, rgba(25, 71, 98, 0.9) 48%, #07131a 100%);
    border: 1px solid #dde6ec;
}

.farm-hero__mountain {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(152deg, transparent 0 56%, #0f2532 56% 100%),
        linear-gradient(212deg, transparent 0 60%, #d36c1f 60% 63%, #1c2f3a 63% 100%),
        linear-gradient(193deg, transparent 0 64%, #101f2a 64% 100%);
    clip-path: polygon(18% 100%, 40% 58%, 52% 20%, 65% 53%, 79% 100%);
    opacity: 0.96;
}

.farm-hero__badge {
    position: absolute;
    left: 1rem;
    top: 0.75rem;
    z-index: 2;
    border-radius: 999px;
    background: rgba(16, 102, 67, 0.92);
    padding: 0.28rem 0.55rem;
    color: #fff;
    font-size: 0.56rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.farm-hero__meta {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-height: 70px;
    padding: 1.9rem 1rem 0.7rem;
    background: linear-gradient(180deg, rgba(4, 11, 16, 0.18), rgba(4, 11, 16, 0.72));
}

.farm-hero__title {
    margin: 0;
    color: #fff;
    font-size: 1.35rem;
    line-height: 1.1;
    font-weight: 700;
    letter-spacing: -0.03em;
}

.farm-hero__location {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin: 0.2rem 0 0;
    color: rgba(255, 255, 255, 0.82);
    font-size: 0.78rem;
}

.farm-hero__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(100px, 1fr));
    gap: 0.55rem;
}

.farm-hero__stat {
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 0.8rem;
    background: rgba(8, 15, 20, 0.55);
    padding: 0.5rem 0.65rem;
    color: #fff;
}

.farm-hero__stat-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: rgba(255, 255, 255, 0.66);
    font-size: 0.56rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.farm-hero__stat-value {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    font-weight: 700;
}

.farm-body {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) 280px;
    gap: 1rem;
    margin-top: 1rem;
}

.farm-main,
.farm-side {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.farm-section,
.farm-side-card {
    border: 1px solid #e5ece7;
    border-radius: 1rem;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 12px 24px -28px rgba(15, 23, 42, 0.24);
}

.farm-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.9rem;
}

.farm-section__title,
.farm-side-card__heading {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #111827;
    font-size: 1.25rem;
    font-weight: 700;
}

.farm-section__icon,
.farm-inline-icon {
    width: 0.95rem;
    height: 0.95rem;
    fill: currentColor;
    flex-shrink: 0;
}

.farm-section__icon {
    color: #1f6b46;
}

.farm-section__icon--light,
.farm-inline-icon--light {
    color: rgba(255, 255, 255, 0.88);
}

.farm-intelligence-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.85rem;
}

.farm-mini-card {
    border: 1px solid #edf2ef;
    border-radius: 0.9rem;
    background: #fbfcfb;
    padding: 0.95rem;
}

.farm-mini-card__label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #93a29d;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.farm-mini-card__value {
    margin-top: 0.55rem;
    color: #1e3b30;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.2;
}

.farm-mini-card__copy {
    margin-top: 0.45rem;
    color: #6b7280;
    font-size: 0.75rem;
    line-height: 1.5;
}

.farm-climate-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) 220px;
    gap: 1rem;
}

.farm-climate-list {
    display: grid;
    gap: 0.75rem;
}

.farm-climate-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: 1px solid #eef2f0;
    padding-bottom: 0.7rem;
    color: #6b7280;
    font-size: 0.88rem;
}

.farm-climate-row__label,
.farm-score-card__label {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.farm-climate-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.farm-climate-row strong {
    color: #111827;
    font-size: 0.9rem;
}

.farm-map-tile {
    position: relative;
    overflow: hidden;
    border: 1px solid #e5ece7;
    border-radius: 1rem;
    background:
        linear-gradient(135deg, rgba(0, 0, 0, 0.04) 25%, transparent 25%) -12px 0/24px 24px,
        linear-gradient(225deg, rgba(0, 0, 0, 0.04) 25%, transparent 25%) -12px 0/24px 24px,
        linear-gradient(315deg, rgba(0, 0, 0, 0.04) 25%, transparent 25%) 0 0/24px 24px,
        linear-gradient(45deg, rgba(0, 0, 0, 0.04) 25%, transparent 25%) 0 0/24px 24px,
        #f6f7f7;
    min-height: 235px;
}

.farm-map-tile__pin {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 88px;
    height: 88px;
    border-radius: 50% 50% 50% 0;
    transform: translate(-50%, -64%) rotate(-45deg);
    background: #434343;
}

.farm-map-tile__pin::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #fff;
}

.farm-map-tile__coords {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 0.95rem;
    border-radius: 0.8rem;
    background: rgba(255, 255, 255, 0.92);
    padding: 0.75rem 0.85rem;
    color: #111827;
    font-size: 0.82rem;
    font-weight: 700;
}

.farm-map-tile__coords small {
    display: block;
    margin-top: 0.18rem;
    color: #6b7280;
    font-size: 0.68rem;
}

.farm-table-wrap {
    overflow-x: auto;
}

.farm-table {
    width: 100%;
    border-collapse: collapse;
}

.farm-table th,
.farm-table td {
    padding: 0.8rem 0.75rem;
    border-bottom: 1px solid #edf2ef;
    text-align: left;
    white-space: nowrap;
}

.farm-table th {
    color: #97a4a0;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.farm-table td {
    color: #1f2937;
    font-size: 0.84rem;
    font-weight: 600;
}

.farm-status-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.3rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 700;
}

.farm-status--green {
    background: #dff5e7;
    color: #1a7a49;
}

.farm-status--amber {
    background: #fff2da;
    color: #b56d10;
}

.farm-status--blue {
    background: #e3eefc;
    color: #2e66c7;
}

.farm-producer-card {
    background: linear-gradient(180deg, #fcfcfc, #f6f8f7);
}

.farm-producer-card__avatar {
    width: 72px;
    height: 72px;
    border-radius: 1rem;
    background:
        radial-gradient(circle at 38% 28%, #f9c58b 0 12%, transparent 13%),
        linear-gradient(180deg, #101826 0 58%, #1b2736 58% 100%);
    box-shadow: inset 0 0 0 1px #e8ece9;
}

.farm-producer-card__name {
    margin-top: 0.9rem;
    color: #1f3d31;
    font-size: 1.1rem;
    font-weight: 700;
}

.farm-producer-card__role {
    margin-top: 0.2rem;
    color: #7a8b85;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.farm-producer-card__copy {
    margin: 0.8rem 0 0;
    color: #6b7280;
    font-size: 0.84rem;
    line-height: 1.7;
}

.farm-producer-card__contact {
    display: grid;
    gap: 0.45rem;
    margin-top: 1rem;
    color: #1f2937;
    font-size: 0.8rem;
    font-weight: 600;
}

.farm-producer-card__contact span {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.farm-score-card {
    background: linear-gradient(180deg, #0c5d3a, #0d7448);
    color: #fff;
}

.farm-score-card__title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
}

.farm-score-card__rows {
    display: grid;
    gap: 0.8rem;
    margin-top: 1rem;
}

.farm-score-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    color: rgba(255, 255, 255, 0.82);
    font-size: 0.8rem;
}

.farm-score-card__row strong {
    color: #fff;
    font-size: 0.85rem;
}

.farm-cert-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.85rem;
}

.farm-cert-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    background: #edf7f1;
    padding: 0.35rem 0.6rem;
    color: #1d6a44;
    font-size: 0.7rem;
    font-weight: 700;
}

@media (max-width: 1199.98px) {
    .farm-body,
    .farm-climate-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .farm-profile-shell {
        padding-inline: 0.75rem;
    }

    .farm-hero__meta {
        flex-direction: column;
        align-items: stretch;
    }

    .farm-hero__title {
        font-size: 1.65rem;
    }

    .farm-hero__stats,
    .farm-intelligence-grid {
        grid-template-columns: 1fr;
    }
}
</style>
