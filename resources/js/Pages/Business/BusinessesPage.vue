<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    OfficeBuilding, Search, Location, Link as LinkIcon, Phone, Message,
    UserFilled, Calendar, FolderOpened,
} from '@element-plus/icons-vue';

const props = defineProps({
    businesses: { type: Array, default: () => [] },
});

const searchQuery = ref('');

function titleCase(value) {
    return String(value || '').replace(/(^|[\s_-])(\w)/g, (m, sep, ch) => sep + ch.toUpperCase());
}

const filteredBusinesses = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.businesses;

    return props.businesses.filter((b) => `${b.business_name} ${b.industry} ${b.business_type} ${b.city} ${b.country}`.toLowerCase().includes(q));
});

function locationLabel(b) {
    return [b.city, b.country].filter(Boolean).join(', ') || 'Location not set';
}
</script>

<template>
    <DesignPreviewLayout title="Businesses">
        <Head title="Businesses" />

        <div class="biz-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <section class="biz-header">
                <div class="biz-header__inner">
                    <div>
                        <div class="biz-kicker">Directory · Bean Origin</div>
                        <h1 class="biz-title">Businesses</h1>
                        <p class="biz-subtitle">{{ businesses.length }} registered business{{ businesses.length === 1 ? '' : 'es' }} on the exchange.</p>
                    </div>
                    <div class="biz-search">
                        <el-icon><Search /></el-icon>
                        <input v-model="searchQuery" type="text" placeholder="Search businesses…">
                    </div>
                </div>
            </section>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="biz-body">
                <div v-if="filteredBusinesses.length" class="biz-grid">
                    <article v-for="b in filteredBusinesses" :key="b.id" class="biz-card">
                        <div class="biz-card__head">
                            <div class="biz-card__logo">
                                <img v-if="b.logo_url" :src="b.logo_url" :alt="b.business_name">
                                <el-icon v-else :size="20"><OfficeBuilding /></el-icon>
                            </div>
                            <div class="biz-card__ident">
                                <div class="biz-card__name">{{ b.business_name }}</div>
                                <div class="biz-card__type">{{ titleCase(b.business_type) }}</div>
                            </div>
                        </div>

                        <p v-if="b.description" class="biz-card__desc">{{ b.description }}</p>

                        <div class="biz-card__meta">
                            <span class="biz-card__meta-item"><el-icon :size="13"><Location /></el-icon> {{ locationLabel(b) }}</span>
                            <span v-if="b.website" class="biz-card__meta-item"><el-icon :size="13"><LinkIcon /></el-icon> {{ b.website }}</span>
                            <span v-if="b.contact_phone" class="biz-card__meta-item"><el-icon :size="13"><Phone /></el-icon> {{ b.contact_phone }}</span>
                            <span v-if="b.contact_email" class="biz-card__meta-item"><el-icon :size="13"><Message /></el-icon> {{ b.contact_email }}</span>
                        </div>

                        <div class="biz-card__foot">
                            <span v-if="b.industry" class="biz-badge">{{ b.industry }}</span>
                            <span v-if="b.year_established" class="biz-card__est"><el-icon :size="12"><Calendar /></el-icon> Est. {{ b.year_established }}</span>
                            <span class="biz-card__members"><el-icon :size="12"><UserFilled /></el-icon> {{ b.members_count || 0 }} member{{ b.members_count === 1 ? '' : 's' }}</span>
                        </div>
                    </article>
                </div>

                <div v-else class="biz-empty">
                    <div class="biz-empty__icon"><el-icon :size="24"><FolderOpened /></el-icon></div>
                    <div class="biz-empty__title">No businesses found</div>
                    <p class="biz-empty__text">
                        {{ searchQuery ? 'Try a different search term.' : 'Registered businesses will appear here.' }}
                    </p>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.biz-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header — edge-to-edge, white, flush top/left/right ─────────────── */
.biz-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
}

.biz-header__inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem;
}

.biz-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.biz-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.biz-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.biz-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    height: 38px;
    width: 260px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--surface-low);
    color: var(--on-surface-var);
    flex-shrink: 0;
}

.biz-search input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8125rem;
    color: var(--on-surface);
    width: 100%;
    font-family: inherit;
}

/* ── Body / grid ─────────────────────────────────────────────────────── */
.biz-body { padding: 1.5rem; }

.biz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.biz-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

.biz-card__head {
    display: flex;
    align-items: center;
    gap: 12px;
}

.biz-card__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--surface-low);
    color: var(--green);
    flex-shrink: 0;
    overflow: hidden;
}
.biz-card__logo img { width: 100%; height: 100%; object-fit: cover; }

.biz-card__ident { min-width: 0; }
.biz-card__name {
    font-size: 0.9375rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.biz-card__type {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--on-surface-var);
    margin-top: 2px;
}

.biz-card__desc {
    margin: 14px 0 0;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.biz-card__meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 14px;
}

.biz-card__meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.biz-card__meta-item :deep(.el-icon) { color: #9ca3af; flex-shrink: 0; }

.biz-card__foot {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 12px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid var(--surface-low);
}

.biz-badge {
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.6875rem;
    font-weight: 700;
}

.biz-card__est,
.biz-card__members {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
    margin-left: auto;
}
.biz-card__est :deep(.el-icon),
.biz-card__members :deep(.el-icon) { color: #9ca3af; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.biz-empty { text-align: center; padding: 4rem 1rem; }

.biz-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid var(--border);
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}

.biz-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.biz-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto; max-width: 320px; line-height: 1.5; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .biz-header__inner { padding: 1.25rem; }
    .biz-search { width: 100%; }
    .biz-body { padding: 1.25rem; }
    .biz-grid { grid-template-columns: 1fr; }
}
</style>
