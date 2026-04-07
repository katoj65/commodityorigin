<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    farmer: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const fullName = computed(() => [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Abebe Bikila');
const locationLabel = computed(() => [props.farmer.sub_county, props.farmer.district, 'Uganda'].filter(Boolean).join(', ') || 'Yirgacheffe, Ethiopia');

const memberSince = computed(() => {
    if (!props.farmer.created_at) {
        return 'Oct 2018';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(props.farmer.created_at));
});

const profileRole = computed(() => {
    if (props.farmer.coffee_type) {
        return 'Master Producer';
    }

    return 'Lead Producer';
});

const producerBrief = computed(
    () =>
        props.farmer.notes ||
        `"Coffee is not just our export, it is our heritage. As a producer in ${props.farmer.district || 'the coffee highlands'}, my commitment remains to the soil and the complex flavors it yields."`,
);

const specialtyBadge = computed(() => {
    if (props.farmer.coffee_type) {
        return `${props.farmer.coffee_type} grade`;
    }

    return 'specialty grade';
});

const performanceStats = computed(() => [
    {
        label: 'Est. Yield',
        value: props.farmer.farm_size ? props.farmer.farm_size.replace(/[^0-9.]/g, '') || '420' : '420',
        suffix: props.farmer.farm_size?.toLowerCase().includes('acre') ? 'Acres' : 'MT',
        accent: '',
        icon: 'harvest',
    },
    {
        label: 'Avg Cup Score',
        value: props.farmer.coffee_type?.toLowerCase() === 'robusta' ? '84.6' : '87.2',
        suffix: '',
        accent: 'producer-stat-value--accent',
        icon: 'cupScore',
    },
    {
        label: 'Sustainability',
        value: '94%',
        suffix: '',
        accent: '',
        icon: 'leaf',
    },
    {
        label: 'Experience',
        value: '20+',
        suffix: 'Yrs',
        accent: '',
        icon: 'award',
    },
]);

const contactDetails = computed(() => [
    { label: 'Telephone', value: props.farmer.telephone || '—' },
    { label: 'Email', value: props.farmer.email || '—' },
    { label: 'District', value: props.farmer.district || '—' },
    { label: 'Subcounty', value: props.farmer.sub_county || '—' },
]);

const productionDetails = computed(() => [
    { label: 'Cooperative', value: props.farmer.cooperative || 'Independent producer' },
    { label: 'Coffee Variety', value: props.farmer.coffee_type || 'Native heirloom' },
    { label: 'Member Since', value: memberSince.value },
]);

const gardenMapQuery = computed(() => {
    const query = [props.farmer.sub_county, props.farmer.district, 'Uganda'].filter(Boolean).join(', ');

    return query || 'Uganda coffee farm';
});

const gardenMapUrl = computed(
    () => `https://www.google.com/maps?q=${encodeURIComponent(gardenMapQuery.value)}&z=12&output=embed`,
);

const credentials = [
    { title: 'Rainforest Alliance', id: 'RA-2024-8832', icon: 'leaf', iconClass: 'text-success-custom' },
    { title: 'Organic Certified', id: 'ORG-ETH-9912', icon: 'award', iconClass: 'text-warning-custom' },
    { title: 'UCDA Licensed', id: 'UCDA-EXP-440', icon: 'shield', iconClass: 'text-primary-soft' },
];

const estates = computed(() => [
    {
        title: props.farmer.cooperative || 'Chelchele Washing Station',
        subtitle: '1,950 - 2,100m',
        tags: ['Washed / Natural', '12 containers ready'],
        identifier: 'ET-GED-001',
        image:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAy4IHPcGUOP1f7W51aY3feFSMZrN5Nr4Y4EhtiFRcYqp3qB-w1Gsz_BpcHEoWUOinkofYUs0SUBqxV3GUaPAykPqNpdga60QZUEVxFgRsxAEM6iEY1KgEBzKfhYzvtnTnVgxopwnS-8iYWo3If9skyzXGlXGD2ACKJT6Bm-P-wmQ6aVNF-F3f1zVX2s4Pwc6HZLk4_uFtFdpjuo_ojH56rzgfxBjN-zd6u3Co6noNishCEMF6pUtnJjRGLCDjXccR9ZX_gQWrTRM1H',
    },
    {
        title: `${fullName.value} Forest`,
        subtitle: '2,000 - 2,200m',
        tags: ['Honey Process', 'In Harvest'],
        identifier: 'ET-GED-844',
        image:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuCJbqUBQZctPHEUZpbB38Ig3KRmrILEcB_cJzXoEENV8n5B5c29dSOjGHgu-dVfsrO7dQassv0XXkILXPQOKfi0p12-tI6H-GQIzG9qmRJ41hqcNk3_MMqn9cIuBo0-3485JIfQj5NBbZ8WLmaGIlMSrePYP_BI-o8fbVHlx37SsvEHyYLiS6HyMAAlnQowOYORWIcQf3Yf_YHQ1EQAMTaHo-xMNepB99vuwWV9zHcyX2qZJOsrZQfWQxcdIS7NlAzO2wQkXsRgd8na',
    },
]);

const iconPaths = {
    verified:
        'M12 2l2.39 2.42 3.37-.49.49 3.37L24 12l-2.42 2.39.49 3.37-3.37.49L12 22l-2.39-2.42-3.37.49-.49-3.37L0 12l2.42-2.39-.49-3.37 3.37-.49L12 2zm-1.05 13.2 6.2-6.2-1.4-1.4-4.8 4.8-2.1-2.1-1.4 1.4 3.5 3.5z',
    location:
        'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z',
    harvest:
        'M18.5 3C13 3 8.8 4.8 6.4 7.3A8.8 8.8 0 004 13.7c0 2.2.7 4.3 2.1 6.1.2.3.5.4.8.4.3 0 .6-.2.8-.4 1.3-1.9 3.2-3.1 5.3-3.1 4 0 7.3-3.3 7.3-7.3V3.9c0-.5-.4-.9-.9-.9z',
    cupScore:
        'M5 5h11a1 1 0 011 1v2h1.5a3.5 3.5 0 010 7H17a6 6 0 01-5 3.92V21h3a1 1 0 110 2H7a1 1 0 110-2h3v-2.08A6 6 0 015 13V6a1 1 0 011-1zm12 5v3h1.5a1.5 1.5 0 000-3H17z',
    leaf:
        'M20 4c-5.5 0-10 1.8-12.6 4.4C4.7 11.1 4 14.2 4 17c0 1 .1 2 .4 3 .1.3.4.5.7.5h.1c.3 0 .6-.2.7-.5.3-.9.8-1.7 1.4-2.4 1.3 1 2.8 1.4 4.4 1.4 4.4 0 8-3.6 8-8V4.8c0-.4-.4-.8-.8-.8z',
    award:
        'M12 2a5 5 0 100 10 5 5 0 000-10zm-3 10.5V22l3-1.8L15 22v-9.5a7 7 0 01-6 0z',
    shield:
        'M12 2l7 3v6c0 5-3.4 9.7-7 11-3.6-1.3-7-6-7-11V5l7-3zm-1 13l5-5-1.4-1.4L11 12.2 9.4 10.6 8 12l3 3z',
    chevronRight:
        'M9.29 6.71a1 1 0 011.42 0l4.59 4.59a1 1 0 010 1.41l-4.59 4.59a1 1 0 11-1.42-1.41L13.17 12 9.29 8.12a1 1 0 010-1.41z',
    farm:
        'M3 11.5L12 4l9 7.5v8a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 19.5v-8zm6 8.5h6V13H9v7z',
    plusCircle:
        'M12 2a10 10 0 100 20 10 10 0 000-20zm1 11h3v-2h-3V8h-2v3H8v2h3v3h2v-3z',
};

const goToAddFarm = () => {
    router.visit(route('farm.create', props.farmer.id));
};
</script>

<template>
    <AppLayout :title="fullName" full-width>
        <div class="producer-directory-page">
            <div class="producer-directory-shell">
                <div v-if="flashSuccess" class="producer-flash-banner">
                    {{ flashSuccess }}
                </div>

                <div class="producer-page-head">
                    <div>
                        <div class="producer-page-kicker">Producer Directory</div>
                        <h1 class="producer-page-title">Farmer Profile</h1>
                    </div>

                    <el-button class="producer-cta-button" @click="goToAddFarm">
                        <el-icon><Plus /></el-icon>
                        <span>Add New Farm</span>
                    </el-button>
                </div>

                <div class="producer-top-grid">
                    <section class="producer-hero-card">
                        <div class="producer-portrait-wrap">
                            <img
                                alt="Farmer"
                                class="producer-portrait"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDqGaybItx8YEtLEuDrfpBy3wt_jJWDxSdoIn-nGYwYW_DBeDzmUUL8zdfy2MxbgqOGwIX1I75_E2VtTkh7okJvlyIhjmWWrm5BvppWAqhcFMMubSNyM8NBzg1hJb2Qh4WtZJzitnr7iiXvNY5T8oJ2xS6X1LY3t9zLrIXEm3Mip_fICb4EHtUYBji6868bfaHZ2onjkktAIJ8m9XyyK6BNCsXblwNOxTQUzk2UQpAdztg0mPf2CCX_KMAPYJdoNR082Hpy8-k1RiBb"
                            />
                            <span class="producer-verified-pill">Verified</span>
                        </div>

                        <div class="producer-hero-content">
                            <div class="producer-hero-header">
                                <div>
                                    <div class="producer-name-row">
                                        <h2 class="producer-name">{{ fullName }}</h2>
                                        <svg class="producer-verified-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.verified" />
                                        </svg>
                                    </div>
                                    <div class="producer-role">{{ profileRole }}</div>
                                    <div class="producer-location-row">
                                        <svg class="producer-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.location" />
                                        </svg>
                                        <span>{{ locationLabel }}</span>
                                    </div>
                                </div>

                                <span class="producer-grade-badge">{{ specialtyBadge }}</span>
                            </div>

                            <div class="producer-brief-kicker">Farmer Brief</div>
                            <p class="producer-brief-copy">{{ producerBrief }}</p>
                        </div>
                    </section>

                    <div class="producer-stat-grid">
                        <article
                            v-for="stat in performanceStats"
                            :key="stat.label"
                            class="producer-stat-card"
                            :class="{ 'producer-stat-card--accent': stat.accent }"
                        >
                            <div class="producer-stat-icon-wrap">
                                <svg class="producer-stat-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths[stat.icon]" />
                                </svg>
                            </div>
                            <div class="producer-stat-label">{{ stat.label }}</div>
                            <div class="producer-stat-value-row">
                                <span class="producer-stat-value" :class="stat.accent">{{ stat.value }}</span>
                                <span v-if="stat.suffix" class="producer-stat-suffix">{{ stat.suffix }}</span>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="producer-content-grid">
                    <div class="producer-main-column">
                        <div class="producer-info-grid">
                            <section class="producer-info-card">
                                <div class="producer-card-heading">
                                    <svg class="producer-card-heading-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.location" />
                                    </svg>
                                    <span>Contact &amp; Location</span>
                                </div>

                                <div class="producer-field-grid">
                                    <div v-for="detail in contactDetails" :key="detail.label" class="producer-field-item">
                                        <div class="producer-field-label">{{ detail.label }}</div>
                                        <div class="producer-field-value">{{ detail.value }}</div>
                                    </div>
                                </div>
                            </section>

                            <section class="producer-info-card">
                                <div class="producer-card-heading">
                                    <svg class="producer-card-heading-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.farm" />
                                    </svg>
                                    <span>Cooperative &amp; Production</span>
                                </div>

                                <div class="producer-coop-name">{{ productionDetails[0].value }}</div>

                                <div class="producer-meta-stack">
                                    <div v-for="detail in productionDetails.slice(1)" :key="detail.label" class="producer-meta-line">
                                        <span class="producer-meta-line-label">{{ detail.label }}</span>
                                        <span class="producer-meta-line-value">{{ detail.value }}</span>
                                    </div>
                                </div>

                                <div class="producer-tag-row">
                                    <span class="producer-tag-chip">{{ props.farmer.coffee_type || 'Native Heirloom' }}</span>
                                    <span class="producer-tag-chip producer-tag-chip--muted">{{ props.farmer.sub_county || 'Kurume' }}</span>
                                </div>
                            </section>
                        </div>

                        <section class="producer-estates-section">
                            <div class="producer-section-header">
                                <h2 class="producer-section-title">Managed Estates</h2>
                                <button type="button" class="producer-link-button">View All Stations</button>
                            </div>

                            <div class="producer-estate-stack">
                                <article v-for="estate in estates" :key="estate.title" class="producer-estate-card">
                                    <div class="producer-estate-media">
                                        <img :alt="estate.title" class="producer-estate-image" :src="estate.image" />
                                    </div>

                                    <div class="producer-estate-content">
                                        <div class="producer-estate-top">
                                            <div>
                                                <h3 class="producer-estate-title">{{ estate.title }}</h3>
                                                <div class="producer-estate-specs">
                                                    <span>{{ estate.subtitle }}</span>
                                                    <span>{{ estate.tags[0] }}</span>
                                                    <span>{{ estate.tags[1] }}</span>
                                                </div>
                                            </div>
                                            <span class="producer-estate-id">ID: {{ estate.identifier }}</span>
                                        </div>
                                    </div>

                                    <button type="button" class="producer-estate-arrow" aria-label="Open estate">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths.chevronRight" />
                                        </svg>
                                    </button>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside class="producer-side-column">
                        <section class="producer-map-card">
                            <div class="producer-map-wrap">
                                <iframe
                                    :src="gardenMapUrl"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Garden location map"
                                ></iframe>
                            </div>
                            <div class="producer-map-overlay">
                                <svg class="producer-map-overlay-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.location" />
                                </svg>
                                <div>
                                    <div class="producer-map-overlay-title">{{ props.farmer.district || 'Gedeo Zone' }}</div>
                                    <div class="producer-map-overlay-copy">{{ locationLabel }}</div>
                                </div>
                            </div>
                        </section>

                        <section class="producer-credentials-card">
                            <div class="producer-card-heading">
                                <svg class="producer-card-heading-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.shield" />
                                </svg>
                                <span>Credentials &amp; Certs</span>
                            </div>

                            <div class="producer-credential-list">
                                <article v-for="credential in credentials" :key="credential.title" class="producer-credential-item">
                                    <div class="producer-credential-main">
                                        <svg class="producer-credential-icon" :class="credential.iconClass" viewBox="0 0 24 24" aria-hidden="true">
                                            <path :d="iconPaths[credential.icon]" />
                                        </svg>
                                        <div>
                                            <div class="producer-credential-title">{{ credential.title }}</div>
                                            <div class="producer-credential-id">ID: {{ credential.id }}</div>
                                        </div>
                                    </div>
                                    <span class="producer-credential-status"></span>
                                </article>
                            </div>

                            <button type="button" class="producer-outline-button">Update Credentials</button>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.producer-directory-page {
    min-height: 100%;
    background: #ffffff;
    color: #10241e;
}

.producer-directory-shell {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0.75rem 0.75rem 1.5rem;
}

.producer-flash-banner {
    margin-bottom: 1rem;
    border: 1px solid #cce6d8;
    border-radius: 1rem;
    background: #edf9f2;
    padding: 0.85rem 1rem;
    color: #1e6a48;
    font-size: 0.9rem;
    font-weight: 600;
}

.producer-page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.producer-page-kicker {
    color: #7b8b88;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.16em;
}

.producer-page-title {
    margin: 0.3rem 0 0;
    color: #111827;
    font-size: 1.7rem;
    line-height: 1.12;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.producer-cta-button {
    --el-button-bg-color: #0f5d3b;
    --el-button-border-color: #0f5d3b;
    --el-button-text-color: #ffffff;
    --el-button-hover-bg-color: #0c5032;
    --el-button-hover-border-color: #0c5032;
    --el-button-hover-text-color: #ffffff;
    height: 44px;
    padding: 0 1.05rem;
    border-radius: 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    box-shadow: 0 16px 26px -20px rgba(15, 93, 59, 0.45);
}

.producer-cta-button :deep(.el-icon) {
    margin-right: 0.35rem;
}

.producer-top-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(260px, 0.82fr);
    gap: 1rem;
    align-items: stretch;
    margin-bottom: 1rem;
}

.producer-hero-card,
.producer-info-card,
.producer-map-card,
.producer-credentials-card,
.producer-estate-card {
    border: 1px solid #e5ece7;
    border-radius: 1rem;
    background: #ffffff;
    box-shadow: 0 16px 28px -30px rgba(15, 23, 42, 0.18);
}

.producer-hero-card {
    display: grid;
    grid-template-columns: 140px minmax(0, 1fr);
    gap: 1.25rem;
    padding: 1.15rem;
}

.producer-portrait-wrap {
    position: relative;
    display: flex;
    flex-direction: column;
}

.producer-portrait {
    width: 100%;
    aspect-ratio: 0.78;
    object-fit: cover;
    border-radius: 0.9rem;
    background: #0f172a;
    box-shadow: 0 14px 24px -22px rgba(15, 23, 42, 0.35);
}

.producer-verified-pill {
    position: absolute;
    left: 0.6rem;
    bottom: 0.6rem;
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: #d8f5e4;
    padding: 0.3rem 0.6rem;
    color: #1c7a4d;
    font-size: 0.62rem;
    font-weight: 800;
}

.producer-hero-content {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
}

.producer-hero-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.producer-name-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.producer-name {
    margin: 0;
    color: #1d3d2f;
    font-size: 1.45rem;
    line-height: 1.12;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.producer-verified-icon {
    width: 1rem;
    height: 1rem;
    fill: #3b82f6;
    flex-shrink: 0;
}

.producer-role {
    margin-top: 0.35rem;
    color: #6b7280;
    font-size: 0.96rem;
    font-weight: 700;
}

.producer-location-row {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.25rem;
    color: #84735d;
    font-size: 0.92rem;
    font-weight: 600;
}

.producer-inline-icon {
    width: 0.95rem;
    height: 0.95rem;
    fill: currentColor;
}

.producer-grade-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 96px;
    border-radius: 999px;
    background: #0f6b47;
    padding: 0.5rem 0.8rem;
    color: #ffffff;
    font-size: 0.6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    text-align: center;
}

.producer-brief-kicker {
    margin-top: 1rem;
    color: #9ca3af;
    font-size: 0.64rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.producer-brief-copy {
    margin: 0.45rem 0 0;
    color: #667085;
    font-size: 1rem;
    line-height: 1.8;
    font-style: italic;
}

.producer-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.producer-stat-card {
    border: 1px solid #e5ece7;
    border-radius: 1rem;
    background: #ffffff;
    padding: 1rem;
    box-shadow: 0 16px 28px -30px rgba(15, 23, 42, 0.18);
    min-height: 136px;
}

.producer-stat-card--accent {
    background: #0d6a46;
    border-color: #0d6a46;
    color: #ffffff;
}

.producer-stat-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.65rem;
    background: #f2f6f4;
    border: 1px solid #e4ebe6;
}

.producer-stat-card--accent .producer-stat-icon-wrap {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.12);
}

.producer-stat-icon {
    width: 1rem;
    height: 1rem;
    fill: #315749;
}

.producer-stat-card--accent .producer-stat-icon {
    fill: #ffffff;
}

.producer-stat-label {
    margin-top: 1.1rem;
    color: #9aa6a1;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.14em;
}

.producer-stat-card--accent .producer-stat-label {
    color: rgba(255, 255, 255, 0.72);
}

.producer-stat-value-row {
    display: flex;
    align-items: baseline;
    gap: 0.35rem;
    margin-top: 0.55rem;
}

.producer-stat-value {
    color: #142b23;
    font-size: 2rem;
    line-height: 1;
    font-weight: 800;
    letter-spacing: -0.04em;
}

.producer-stat-card--accent .producer-stat-value,
.producer-stat-value--accent {
    color: #ffffff;
}

.producer-stat-suffix {
    color: #6b7280;
    font-size: 0.92rem;
    font-weight: 700;
}

.producer-stat-card--accent .producer-stat-suffix {
    color: rgba(255, 255, 255, 0.78);
}

.producer-content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(280px, 0.82fr);
    gap: 1rem;
}

.producer-main-column,
.producer-side-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.producer-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.producer-info-card,
.producer-credentials-card {
    padding: 1.1rem;
}

.producer-card-heading {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: #94a3b8;
    font-size: 0.66rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.14em;
}

.producer-card-heading-icon {
    width: 0.95rem;
    height: 0.95rem;
    fill: #315749;
}

.producer-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.25rem;
    margin-top: 1.2rem;
}

.producer-field-label,
.producer-meta-line-label {
    color: #9ca3af;
    font-size: 0.58rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.producer-field-value,
.producer-meta-line-value {
    margin-top: 0.3rem;
    color: #111827;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.producer-coop-name {
    margin-top: 1rem;
    color: #1d4d39;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.producer-meta-stack {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 1rem;
}

.producer-meta-line {
    display: flex;
    flex-direction: column;
    gap: 0.22rem;
    min-width: 0;
}

.producer-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    margin-top: 1rem;
}

.producer-tag-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 0.45rem;
    background: #dcf2e6;
    padding: 0.35rem 0.55rem;
    color: #23734c;
    font-size: 0.68rem;
    font-weight: 800;
}

.producer-tag-chip--muted {
    background: #eef3f1;
    color: #5f7a6e;
}

.producer-map-card {
    position: relative;
    overflow: hidden;
    min-height: 330px;
}

.producer-map-wrap,
.producer-map-wrap iframe {
    width: 100%;
    height: 100%;
}

.producer-map-wrap iframe {
    border: 0;
    display: block;
}

.producer-map-overlay {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    border-radius: 0.9rem;
    background: rgba(11, 31, 23, 0.72);
    backdrop-filter: blur(8px);
    padding: 0.8rem 0.9rem;
    color: #ffffff;
}

.producer-map-overlay-icon {
    width: 1rem;
    height: 1rem;
    fill: #c5f2d8;
    flex-shrink: 0;
}

.producer-map-overlay-title {
    font-size: 0.84rem;
    font-weight: 800;
}

.producer-map-overlay-copy {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.78);
}

.producer-credential-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
}

.producer-credential-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border-bottom: 1px solid #eef2f0;
    padding-bottom: 0.75rem;
}

.producer-credential-item:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.producer-credential-main {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    min-width: 0;
}

.producer-credential-icon {
    width: 1.1rem;
    height: 1.1rem;
    fill: currentColor;
    margin-top: 0.05rem;
    flex-shrink: 0;
}

.producer-credential-title {
    color: #111827;
    font-size: 0.88rem;
    font-weight: 700;
}

.producer-credential-id {
    margin-top: 0.2rem;
    color: #94a3b8;
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.producer-credential-status {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 999px;
    background: #22c55e;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
    flex-shrink: 0;
}

.producer-outline-button {
    width: 100%;
    margin-top: 1rem;
    border: 1px dashed #d8dde2;
    border-radius: 0.8rem;
    background: #fbfcfd;
    padding: 0.8rem 1rem;
    color: #8c98a7;
    font-size: 0.8rem;
    font-weight: 700;
}

.producer-estates-section {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.producer-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.producer-section-title {
    margin: 0;
    color: #111827;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.03em;
}

.producer-link-button {
    border: 0;
    background: transparent;
    color: #315749;
    font-size: 0.78rem;
    font-weight: 700;
}

.producer-estate-stack {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.producer-estate-card {
    display: grid;
    grid-template-columns: 76px minmax(0, 1fr) 40px;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem;
}

.producer-estate-media {
    width: 76px;
    height: 76px;
    overflow: hidden;
    border-radius: 0.85rem;
    background: #edf2f0;
}

.producer-estate-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.producer-estate-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.8rem;
}

.producer-estate-title {
    margin: 0;
    color: #23523d;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.35;
}

.producer-estate-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-top: 0.35rem;
    color: #6b7280;
    font-size: 0.78rem;
    font-weight: 600;
}

.producer-estate-id {
    color: #9ca3af;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.producer-estate-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2rem;
    height: 2.2rem;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    background: #ffffff;
}

.producer-estate-arrow svg {
    width: 0.95rem;
    height: 0.95rem;
    fill: #6b7280;
}

.text-success-custom {
    color: #1f8a57;
}

.text-primary-soft {
    color: #2563eb;
}

.text-warning-custom {
    color: #f97316;
}

@media (max-width: 1199.98px) {
    .producer-top-grid,
    .producer-content-grid {
        grid-template-columns: 1fr;
    }

    .producer-map-card {
        min-height: 280px;
    }
}

@media (max-width: 767.98px) {
    .producer-directory-shell {
        padding-inline: 0.75rem;
    }

    .producer-page-head {
        flex-direction: column;
        align-items: stretch;
    }

    .producer-cta-button {
        width: 100%;
    }

    .producer-hero-card,
    .producer-info-grid,
    .producer-field-grid,
    .producer-stat-grid,
    .producer-estate-card {
        grid-template-columns: 1fr;
    }

    .producer-portrait-wrap {
        max-width: 180px;
    }

    .producer-page-title {
        font-size: 1.45rem;
    }

    .producer-name {
        font-size: 1.25rem;
    }

    .producer-hero-header {
        flex-direction: column;
    }

    .producer-estate-media {
        width: 100%;
        height: 160px;
    }

    .producer-estate-arrow {
        justify-self: end;
    }
}
</style>
