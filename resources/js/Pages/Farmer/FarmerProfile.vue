<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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
const locationLabel = computed(() => [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') || 'Gedeo Highlands, Ethiopia');
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
        return `${props.farmer.coffee_type} Producer`;
    }

    return 'Lead Producer';
});

const stats = computed(() => [
    { label: 'Total Land', value: props.farmer.farm_size || '124', suffix: 'Hectares', accent: '', icon: 'farm' },
    { label: 'Est. Yield', value: '45,200', suffix: 'kg/season', accent: '', icon: 'harvest' },
    { label: 'Avg. Cup Score', value: '87.5', suffix: 'SCA Points', accent: '', icon: 'cupScore' },
    { label: 'Sustainability', value: 'A+', suffix: 'Verified', accent: 'text-success-custom', icon: 'leaf' },
]);

const philosophyText = computed(
    () =>
        props.farmer.notes ||
        'Abebe represents the third generation of specialty coffee cultivation in the Gedeo zone. His approach blends ancestral knowledge of shade-grown forest coffee with modern anaerobic fermentation techniques. Abebe believes that the health of the soil is directly reflected in the complexity of the cup, dedicating 15% of his land exclusively to biodiversity corridors.',
);

const credentials = [
    { title: 'Rainforest Alliance', icon: 'leaf', iconClass: 'text-success-custom' },
    { title: 'Organic EU', icon: 'award', iconClass: 'text-warning-custom' },
    { title: 'UCDA Verified', icon: 'shield', iconClass: 'text-primary-soft' },
];

const estates = computed(() => [
    {
        title: props.farmer.cooperative || 'Chelchele Washing Station',
        subtitle: `1,950 - 2,100 masl • ${locationLabel.value}`,
        tags: ['Washed', props.farmer.coffee_type || 'Heirloom'],
        value: props.farmer.farm_size || '82 Ha',
        meta: 'Primary Site',
        image:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAy4IHPcGUOP1f7W51aY3feFSMZrN5Nr4Y4EhtiFRcYqp3qB-w1Gsz_BpcHEoWUOinkofYUs0SUBqxV3GUaPAykPqNpdga60QZUEVxFgRsxAEM6iEY1KgEBzKfhYzvtnTnVgxopwnS-8iYWo3If9skyzXGlXGD2ACKJT6Bm-P-wmQ6aVNF-F3f1zVX2s4Pwc6HZLk4_uFtFdpjuo_ojH56rzgfxBjN-zd6u3Co6noNishCEMF6pUtnJjRGLCDjXccR9ZX_gQWrTRM1H',
    },
    {
        title: `${fullName.value} Highlands Block`,
        subtitle: `2,200 - 2,350 masl • ${props.farmer.district || 'Regional Block'}`,
        tags: ['Natural', 'Honey'],
        value: '42 Ha',
        meta: 'Experimental',
        image:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuCJbqUBQZctPHEUZpbB38Ig3KRmrILEcB_cJzXoEENV8n5B5c29dSOjGHgu-dVfsrO7dQassv0XXkILXPQOKfi0p12-tI6H-GQIzG9qmRJ41hqcNk3_MMqn9cIuBo0-3485JIfQj5NBbZ8WLmaGIlMSrePYP_BI-o8fbVHlx37SsvEHyYLiS6HyMAAlnQowOYORWIcQf3Yf_YHQ1EQAMTaHo-xMNepB99vuwWV9zHcyX2qZJOsrZQfWQxcdIS7NlAzO2wQkXsRgd8na',
    },
]);

const iconPaths = {
    verified:
        'M12 2l2.39 2.42 3.37-.49.49 3.37L24 12l-2.42 2.39.49 3.37-3.37.49L12 22l-2.39-2.42-3.37.49-.49-3.37L0 12l2.42-2.39-.49-3.37 3.37-.49L12 2zm-1.05 13.2 6.2-6.2-1.4-1.4-4.8 4.8-2.1-2.1-1.4 1.4 3.5 3.5z',
    location:
        'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z',
    calendar:
        'M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1a2 2 0 012 2v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6a2 2 0 012-2h1V3a1 1 0 011-1zm12 8H5v8a1 1 0 001 1h12a1 1 0 001-1v-8zM6 6a1 1 0 00-1 1v1h14V7a1 1 0 00-1-1H6z',
    userCircle:
        'M12 2a10 10 0 100 20 10 10 0 000-20zm0 5a3.25 3.25 0 110 6.5A3.25 3.25 0 0112 7zm0 13a7.96 7.96 0 01-5.23-1.94 5.98 5.98 0 0110.46 0A7.96 7.96 0 0112 20z',
    plusCircle:
        'M12 2a10 10 0 100 20 10 10 0 000-20zm1 11h3v-2h-3V8h-2v3H8v2h3v3h2v-3z',
    farm:
        'M3 11.5L12 4l9 7.5v8a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 19.5v-8zm6 8.5h6V13H9v7z',
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
};
</script>

<template>
    <AppLayout :title="fullName" full-width>
        <div class="farmer-bootstrap-page min-vh-100 bg-white">
            <div class="container-fluid px-2 px-md-3 px-xl-4 py-3 py-xl-4">
                <div
                    v-if="flashSuccess"
                    class="alert alert-success border-0 rounded-3 mb-4 shadow-sm small fw-semibold"
                    role="alert"
                >
                    {{ flashSuccess }}
                </div>

                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-4 gap-xl-5 mb-5">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4 gap-sm-5">
                        <div class="passport-photo-wrap flex-shrink-0">
                            <img
                                alt="Farmer"
                                class="passport-photo"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDqGaybItx8YEtLEuDrfpBy3wt_jJWDxSdoIn-nGYwYW_DBeDzmUUL8zdfy2MxbgqOGwIX1I75_E2VtTkh7okJvlyIhjmWWrm5BvppWAqhcFMMubSNyM8NBzg1hJb2Qh4WtZJzitnr7iiXvNY5T8oJ2xS6X1LY3t9zLrIXEm3Mip_fICb4EHtUYBji6868bfaHZ2onjkktAIJ8m9XyyK6BNCsXblwNOxTQUzk2UQpAdztg0mPf2CCX_KMAPYJdoNR082Hpy8-k1RiBb"
                            />
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h1 class="profile-title mb-0">{{ fullName }}</h1>
                                <svg class="verified-badge" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.verified" />
                                </svg>
                            </div>

                            <div class="d-flex align-items-center gap-2 profile-location mb-4">
                                <svg class="location-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths.location" />
                                </svg>
                                <span>{{ locationLabel }}</span>
                            </div>

                            <div class="d-flex gap-4 gap-sm-5 flex-wrap">
                                <div class="d-flex align-items-start gap-2">
                                    <svg class="meta-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.calendar" />
                                    </svg>
                                    <div>
                                        <div class="meta-label">Member Since</div>
                                        <div class="meta-value">{{ memberSince }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <svg class="meta-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths.userCircle" />
                                    </svg>
                                    <div>
                                        <div class="meta-label">Role</div>
                                        <div class="meta-value">{{ profileRole }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a
                            :href="farmer.email ? `mailto:${farmer.email}` : `tel:${farmer.telephone}`"
                            class="btn btn-outline-slate-custom"
                        >
                            Contact Producer
                        </a>
                        <Link :href="route('farmer.create')" class="btn btn-primary-emerald d-inline-flex align-items-center justify-content-center gap-2">
                            <svg class="btn-plus-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path :d="iconPaths.plusCircle" />
                            </svg>
                            <span>Add New Farm</span>
                        </Link>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div v-for="stat in stats" :key="stat.label" class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card h-100">
                            <div class="mb-3 d-flex align-items-center justify-content-between gap-2">
                                <div class="stat-label mb-0">{{ stat.label }}</div>
                                <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :d="iconPaths[stat.icon]" />
                                </svg>
                            </div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="stat-value" :class="stat.accent">{{ stat.value }}</span>
                                <span class="stat-unit">{{ stat.suffix }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 g-xl-5 mb-5">
                    <div class="col-12 col-xl-8">
                        <div class="section-divider mb-4">Producer Philosophy</div>
                        <p class="philosophy-text mb-4">
                            {{ philosophyText }}
                        </p>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="gallery-card">
                                    <img
                                        alt="Gallery 1"
                                        class="gallery-image"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq5mGiuy5pG7uMwRhcCqkPuZyVCc6Gef0osUNwkJ3XYvbO7sDBE6VgL2OZDxC4Q1_UIkfBi_6U2S9GPJDyiV6I_NE9OafvV4SYVIsGE8zI38g9hkMTo1QyEit0MNP_NG_mNDLrRhhtq2JkF7AKwtGyumggy4lFihqGviDjy1Rbwlsr4nvXOcZ9Udr6SYfG-Yq-91cbkkOC_Qsye5UnKJCxTsqxIshhMd4-Svq478wP2YaATbr-I48xPAedW9YjxfeTHMcJyQe7Qt-t"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="gallery-card">
                                    <img
                                        alt="Gallery 2"
                                        class="gallery-image"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuBk2iQYI4oisrDDBuwH_0XW6NT6jgpkvyO_UgXm4k4MtD4lSv0s64IIf7YMRfuarD4JlEjWQZW_vNWfr26tDHohY-UwcwCZfSLvBkv7dBXfWxHzCBoDrQNuaDfsVUyfVBJT4npsJ53ckg4ZhQj-MqcPQe7bktKzyU_Bup0-wiHhIJ4_TVLK8w38gE6lHyy5HVvoy05_jyNoGlwWKC_9duthAW-FYPOp3oaRnfD-uYyaseI0OfDqf0o_ChBcAJJG9tp-xMHq3fbyqRmy"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="gallery-card">
                                    <img
                                        alt="Gallery 3"
                                        class="gallery-image"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8zpvApZVvL6pXk7ku5jgLhS3aAlIVShtGgK_YQv7hvR83-Pxa6yeCMdAmMs3n4t9B8qh5HMniNjeGD7RpTVA2LA6Z-a3ODxLCJrx457NxymWGOzTBYdCKc4SN-gDescvt_ZfwuvW_0NfMsjnt6OSj_U4bYL5cRs3C19LdLFtgWdylX14Aas4Z1oUB11t4fjKyDc4Psq4pWxnAdTP-aDskQrGpWYxjKG8MlNpRkw0Rf3lmYwkxnLc6MeZrX1enlrIS4RQ5E4DKOCZy"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="credentials-panel">
                            <div class="credentials-heading">Technical Credentials</div>

                            <div v-for="credential in credentials" :key="credential.title" class="credential-card">
                                <div class="d-flex align-items-center gap-3">
                                    <svg class="credential-icon" :class="credential.iconClass" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="iconPaths[credential.icon]" />
                                    </svg>
                                    <span class="credential-title">{{ credential.title }}</span>
                                </div>
                                <span class="badge-active">Active</span>
                            </div>

                            <div class="credentials-footer">
                                <div class="d-flex justify-content-between align-items-center audit-row">
                                    <span>LAST AUDIT</span>
                                    <span>12 JAN 2024</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section>
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                        <h2 class="managed-title mb-0 d-flex align-items-center gap-2">
                            <svg class="managed-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path :d="iconPaths.farm" />
                            </svg>
                            <span>Managed Estates</span>
                        </h2>
                        <span class="managed-count d-inline-flex align-items-center gap-1.5">
                            <svg class="managed-count-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path :d="iconPaths.location" />
                            </svg>
                            <span>{{ estates.length }} Locations Listed</span>
                        </span>
                    </div>

                    <div v-for="estate in estates" :key="estate.title" class="estate-card">
                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 gap-md-0">
                            <div class="estate-thumb-wrap">
                                <img :alt="estate.title" class="estate-img" :src="estate.image" />
                            </div>

                            <div class="estate-info flex-grow-1">
                                <h4 class="estate-title">{{ estate.title }}</h4>
                                <p class="estate-subtitle mb-0">{{ estate.subtitle }}</p>
                            </div>

                            <div class="estate-tags me-md-4">
                                <span v-for="tag in estate.tags" :key="tag" class="estate-tag">{{ tag }}</span>
                            </div>

                            <div class="estate-metrics text-md-end me-md-4">
                                <p class="estate-value mb-0">{{ estate.value }}</p>
                                <p class="estate-meta mb-0">{{ estate.meta }}</p>
                            </div>

                            <svg class="estate-chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <path :d="iconPaths.chevronRight" />
                            </svg>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.farmer-bootstrap-page {
    --primary-emerald: #065f46;
    --primary-emerald-hover: #044e3a;
    --primary-emerald-light: rgba(6, 95, 70, 0.05);
    --slate-text: #64748b;
    --slate-border: #e2e8f0;
}

.farmer-bootstrap-page,
.farmer-bootstrap-page * {
    font-family: Inter, sans-serif;
}

.container-fluid {
    width: 100%;
    max-width: none;
}

.passport-photo-wrap {
    width: 100px;
    height: 100px;
}

.passport-photo {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f1f5f9;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.profile-title {
    font-size: 1.75rem;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.verified-badge {
    width: 20px;
    height: 20px;
    fill: #3b82f6;
}

.location-icon {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

.meta-icon {
    width: 16px;
    height: 16px;
    margin-top: 0.1rem;
    fill: #94a3b8;
    flex-shrink: 0;
}

.meta-label {
    color: var(--slate-text);
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.meta-value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
}

.btn-primary-emerald {
    min-width: 200px;
    background-color: var(--primary-emerald);
    border-color: var(--primary-emerald);
    color: #fff;
    font-weight: 700;
    font-size: 0.875rem;
    padding: 0.625rem 1.25rem;
}

.btn-primary-emerald:hover {
    background-color: var(--primary-emerald-hover);
    border-color: var(--primary-emerald-hover);
    color: #fff;
}

.btn-outline-slate-custom {
    min-width: 194px;
    border: 1px solid var(--slate-border);
    color: #0f172a;
    font-weight: 700;
    font-size: 0.875rem;
    padding: 0.625rem 1.25rem;
}

.btn-outline-slate-custom:hover {
    background-color: #f8fafc;
    border-color: var(--slate-border);
    color: #0f172a;
}

.btn-plus-icon {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

.stat-card {
    border: 1px solid var(--slate-border);
    border-radius: 4px;
    padding: 1.25rem;
}

.stat-label {
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--slate-text);
    margin-bottom: 0.75rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    color: #0f172a;
}

.stat-icon {
    width: 18px;
    height: 18px;
    fill: #94a3b8;
    flex-shrink: 0;
}

.text-success-custom {
    color: #2d6a4f;
}

.stat-unit {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--slate-text);
}

.section-divider {
    display: flex;
    align-items: center;
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
}

.section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--slate-border);
    margin-left: 1rem;
}

.philosophy-text {
    color: var(--slate-text);
    font-size: 0.95rem;
    line-height: 1.9;
}

.gallery-card {
    aspect-ratio: 16 / 10;
    border-radius: 4px;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.credentials-panel {
    background: #f8fafc;
    border: 1px solid var(--slate-border);
    border-radius: 4px;
    padding: 1.5rem;
}

.credentials-heading {
    color: var(--slate-text);
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-bottom: 1.25rem;
}

.credential-card {
    background: #fff;
    border: 1px solid var(--slate-border);
    padding: 0.75rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.credential-icon {
    width: 20px;
    height: 20px;
    fill: currentColor;
    flex-shrink: 0;
}

.credential-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0f172a;
}

.badge-active {
    font-size: 0.56rem;
    font-weight: 800;
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.credentials-footer {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--slate-border);
}

.audit-row {
    font-size: 0.625rem;
    font-weight: 700;
    color: var(--slate-text);
    letter-spacing: 0.08em;
}

.managed-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
}

.managed-count {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--slate-text);
}

.managed-icon,
.managed-count-icon {
    width: 16px;
    height: 16px;
    fill: currentColor;
    flex-shrink: 0;
}

.estate-card {
    border: 1px solid var(--slate-border);
    border-radius: 4px;
    padding: 1rem;
    transition: all 0.2s;
    cursor: pointer;
    margin-bottom: 0.75rem;
    background: #fff;
}

.estate-card:hover {
    border-color: rgba(6, 95, 70, 0.3);
    background-color: #fbfcfd;
}

.estate-thumb-wrap {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
}

.estate-img {
    width: 100%;
    height: 100%;
    border-radius: 4px;
    object-fit: cover;
}

.estate-info {
    margin-left: 1.5rem;
}

.estate-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.estate-subtitle {
    font-size: 0.75rem;
    color: var(--slate-text);
}

.estate-tags {
    display: flex;
    gap: 0.5rem;
}

.estate-tag {
    font-size: 0.56rem;
    font-weight: 800;
    padding: 0.35rem 0.5rem;
    background: #f1f5f9;
    color: var(--slate-text);
    border-radius: 4px;
    text-transform: uppercase;
}

.estate-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: #0f172a;
}

.estate-meta {
    font-size: 0.625rem;
    font-weight: 700;
    color: var(--slate-text);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.estate-chevron {
    width: 20px;
    height: 20px;
    fill: #cbd5e1;
    flex-shrink: 0;
}

.text-primary-soft {
    color: #2563eb;
}

.text-warning-custom {
    color: #d97706;
}

@media (max-width: 767.98px) {
    .profile-title {
        font-size: 1.375rem;
    }

    .estate-info {
        margin-left: 0;
    }
}
</style>
