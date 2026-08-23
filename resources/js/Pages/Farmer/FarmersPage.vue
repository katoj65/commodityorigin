<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    CirclePlus, Location, Message, Refresh,
    Search, UserFilled, View, Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    farmers: { type: Array, default: () => [] },
});

const TONES = ['fd-tone-a', 'fd-tone-b', 'fd-tone-c'];
const toneClass = (id) => TONES[id % TONES.length];

/* ── Filters ───────────────────────────────────────────────────── */
const search = ref('');
const districtF = ref('All');
const verificationF = ref('All');
const statusF = ref('All');

const uniqueDistricts = computed(() => ['All', ...new Set(props.farmers.map(f => f.district).filter(Boolean))].sort());
const uniqueVerifications = computed(() => ['All', ...new Set(props.farmers.map(f => f.verification_status).filter(Boolean))].sort());

const verificationLabel = (v) => v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Pending';
const statusLabel = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : 'Active';

const rows = computed(() => props.farmers.map(farmer => ({
    ...farmer,
    fullName: farmer.full_name || [farmer.first_name, farmer.last_name].filter(Boolean).join(' ') || 'Unnamed Farmer',
    location: [farmer.subcounty, farmer.district].filter(Boolean).join(', ') || 'Location pending',
    farmsCount: farmer.farms_count || 0,
})));

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();
    return rows.value.filter(f => {
        const hay = [f.fullName, f.district, f.subcounty, f.cooperative?.name].filter(Boolean).join(' ').toLowerCase();
        const mS = !term || hay.includes(term);
        const mD = districtF.value === 'All' || f.district === districtF.value;
        const mV = verificationF.value === 'All' || f.verification_status === verificationF.value;
        const mSt = statusF.value === 'All' || f.status === statusF.value;
        return mS && mD && mV && mSt;
    });
});

const resetFilters = () => { search.value = ''; districtF.value = 'All'; verificationF.value = 'All'; statusF.value = 'All'; };
</script>

<template>
    <DesignPreviewLayout title="Farmers Directory">
        <Head title="Farmers Directory" />

        <div class="fd-page">

            <div class="fd-header">
                <div class="fd-header__decor" aria-hidden="true"></div>
                <div class="fd-header__text">
                    <h1 class="fd-title">Farmers Directory</h1>
                    <p class="fd-subtitle">Browse every registered coffee farmer, their origin, and their cooperative on the Bean Origin exchange.</p>
                </div>
                <Link :href="route('farmer.create')" class="fd-btn-primary">
                    <el-icon><CirclePlus /></el-icon> Add New Farmer
                </Link>
            </div>

            <div class="fd-filters">
                <el-input
                    v-model="search"
                    class="fd-filters__search"
                    placeholder="Search farmers by name, origin or cooperative…"
                    aria-label="Search farmers"
                    clearable
                >
                    <template #prefix><el-icon><Search /></el-icon></template>
                </el-input>

                <div class="fd-filters__pills">
                    <el-select v-model="districtF" class="fd-pill-select" aria-label="Filter by origin">
                        <el-option v-for="d in uniqueDistricts" :key="d" :label="d === 'All' ? 'All Origins' : d" :value="d" />
                    </el-select>

                    <el-select v-model="verificationF" class="fd-pill-select" aria-label="Filter by verification">
                        <el-option v-for="v in uniqueVerifications" :key="v" :label="v === 'All' ? 'All Verification' : verificationLabel(v)" :value="v" />
                    </el-select>

                    <el-select v-model="statusF" class="fd-pill-select" aria-label="Filter by status">
                        <el-option label="All Status" value="All" />
                        <el-option label="Active" value="active" />
                        <el-option label="Inactive" value="inactive" />
                    </el-select>

                    <button type="button" class="fd-tune-btn" aria-label="Reset filters" title="Reset filters" @click="resetFilters">
                        <el-icon><Refresh /></el-icon>
                    </button>
                </div>
            </div>

            <div class="fd-count">Showing <strong>{{ filteredRows.length }}</strong> of <strong>{{ rows.length }}</strong> farmers</div>

            <div v-if="!filteredRows.length" class="fd-empty">
                <div class="fd-empty__icon"><el-icon :size="22"><Warning /></el-icon></div>
                <div class="fd-empty__title">No farmers match your filters</div>
                <p class="fd-empty__text">Try adjusting your search term or filter selections.</p>
                <button type="button" class="fd-btn-outline" @click="resetFilters">Reset Filters</button>
            </div>

            <div v-else class="fd-list">
                <div v-for="farmer in filteredRows" :key="farmer.id" class="fd-row">
                    <div class="fd-row__avatar" :class="toneClass(farmer.id)">
                        <el-icon><UserFilled /></el-icon>
                    </div>

                    <div class="fd-row__identity">
                        <h3 class="fd-row__name">{{ farmer.fullName }}</h3>
                        <div class="fd-row__meta-line">
                            <span class="fd-row__location"><el-icon><Location /></el-icon>{{ farmer.location }}</span>
                            <span class="fd-row__status" :class="farmer.status === 'active' ? 'fd-row__status--active' : 'fd-row__status--inactive'">
                                <i></i>{{ statusLabel(farmer.status) }}
                            </span>
                        </div>
                    </div>

                    <div class="fd-row__specs">
                        <div class="fd-row__spec">
                            <span class="fd-row__spec-label">Verification</span>
                            <strong class="fd-row__spec-value">{{ verificationLabel(farmer.verification_status) }}</strong>
                        </div>
                        <div class="fd-row__spec">
                            <span class="fd-row__spec-label">Farms</span>
                            <strong class="fd-row__spec-value">{{ farmer.farmsCount }} registered</strong>
                        </div>
                        <div class="fd-row__spec">
                            <span class="fd-row__spec-label">Cooperative</span>
                            <strong class="fd-row__spec-value">{{ farmer.cooperative?.name || 'Independent' }}</strong>
                        </div>
                    </div>

                    <div class="fd-row__actions">
                        <Link :href="route('farmer.show', farmer.id)" class="fd-btn-outline">
                            <el-icon><View /></el-icon> View Profile
                        </Link>
                        <a
                            v-if="farmer.email"
                            :href="`mailto:${farmer.email}`"
                            class="fd-contact-btn"
                            :aria-label="`Email ${farmer.fullName}`"
                            :title="`Email ${farmer.fullName}`"
                        >
                            <el-icon><Message /></el-icon>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.fd-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Header ────────────────────────────────────────────────────── */
.fd-header {
    position: relative; display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; flex-wrap: wrap; overflow: hidden;
}
.fd-header__decor {
    position: absolute; top: -140px; right: -120px; width: 360px; height: 280px;
    background: var(--dp-primary); opacity: 0.06; border-radius: 50%; filter: blur(70px);
    pointer-events: none; z-index: 0;
}
.fd-header__text { position: relative; z-index: 1; max-width: 620px; }
.fd-kicker {
    font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--dp-secondary); margin-bottom: 4px;
}
.fd-title { font-size: clamp(1.375rem, 1.05rem + 1.2vw, 1.75rem); font-weight: 700; letter-spacing: -0.02em; color: var(--dp-on-surface); margin: 0 0 4px; }
.fd-subtitle { font-size: 0.9375rem; color: var(--dp-on-surface-variant); margin: 0; line-height: 1.5; }

.fd-btn-primary {
    position: relative; z-index: 1;
    display: inline-flex; align-items: center; gap: 6px; background: var(--dp-primary); color: var(--dp-on-primary);
    border: none; border-radius: 999px; font-size: 0.875rem; font-weight: 600; padding: 10px 20px;
    text-decoration: none; white-space: nowrap; transition: opacity .15s ease;
}
.fd-btn-primary:hover { opacity: 0.88; color: var(--dp-on-primary); }
.fd-btn-primary:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Filters ───────────────────────────────────────────────────── */
.fd-filters {
    display: flex; flex-direction: column; gap: 14px;
    background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow); padding: 20px;
}
@media (min-width: 768px) { .fd-filters { flex-direction: row; align-items: center; } }

.fd-filters__search { flex: 1; }
.fd-filters__search :deep(.el-input__wrapper) {
    border-radius: 999px; box-shadow: none; background: var(--dp-surface-container-low); padding: 0 16px; height: 44px;
}
.fd-filters__search :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset; background: var(--dp-surface-container-lowest); }
.fd-filters__search :deep(.el-input__inner) { font-family: var(--dp-font-sans); color: var(--dp-on-surface); }
.fd-filters__search :deep(.el-input__prefix) { color: var(--dp-on-surface-variant); }

.fd-filters__pills { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.fd-pill-select { width: 150px; }
.fd-pill-select :deep(.el-select__wrapper) {
    border-radius: 999px; box-shadow: none !important; background: var(--dp-surface-container-low);
    min-height: 40px; font-family: var(--dp-font-sans);
}
.fd-pill-select :deep(.el-select__wrapper:hover) { background: var(--dp-surface-container); }
.fd-pill-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; background: var(--dp-surface-container-lowest); }
.fd-pill-select :deep(.el-select__selected-item) { color: var(--dp-on-surface); }

.fd-tune-btn {
    width: 40px; height: 40px; border-radius: 999px; border: none; flex-shrink: 0;
    background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant);
    display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s ease;
}
.fd-tune-btn:hover { background: var(--dp-surface-container); }
.fd-tune-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.fd-btn-outline {
    display: inline-flex; align-items: center; gap: 6px; height: 38px; border-radius: 10px;
    font-size: 0.8125rem; font-weight: 600; padding: 0 14px; white-space: nowrap; text-decoration: none;
    cursor: pointer; transition: background .15s ease;
    background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface);
}
.fd-btn-outline:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.fd-btn-outline:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.fd-count { font-size: 0.875rem; color: var(--dp-on-surface-variant); }
.fd-count strong { color: var(--dp-on-surface); font-weight: 700; }

/* ── Empty state ───────────────────────────────────────────────── */
.fd-empty {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px;
    background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow); padding: 48px 24px;
}
.fd-empty__icon {
    width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    background: var(--dp-error-container); color: var(--dp-error); margin-bottom: 6px;
}
.fd-empty__title { font-size: 1rem; font-weight: 700; color: var(--dp-on-surface); }
.fd-empty__text { font-size: 0.875rem; color: var(--dp-on-surface-variant); margin: 0 0 8px; }

/* ── List ──────────────────────────────────────────────────────── */
.fd-list { display: flex; flex-direction: column; gap: 14px; }

.fd-row {
    background: var(--dp-surface-container-lowest); border-radius: 16px; box-shadow: var(--dp-card-shadow);
    padding: 16px 20px; display: flex; align-items: center; gap: 24px;
    transition: box-shadow .15s ease, transform .15s ease;
}
.fd-row:hover { box-shadow: 0 2px 4px rgba(39,19,16,.05), 0 12px 24px -14px rgba(39,19,16,.18); transform: translateY(-1px); }

.fd-row__avatar {
    width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; background-image: var(--fd-tone-gradient);
}
.fd-row__avatar .el-icon { font-size: 1.75rem; color: rgba(255,255,255,.85); }
.fd-tone-a { --fd-tone-gradient: linear-gradient(150deg, var(--dp-primary-container), var(--dp-primary)); }
.fd-tone-b { --fd-tone-gradient: linear-gradient(150deg, #3a5a3f, var(--dp-secondary)); }
.fd-tone-c { --fd-tone-gradient: linear-gradient(150deg, #8a6a3f, #4a3520); }

.fd-row__identity { flex: 1 1 220px; min-width: 0; }
.fd-row__name { font-size: 1.0625rem; font-weight: 700; color: var(--dp-on-surface); margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fd-row__meta-line { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.fd-row__location { display: inline-flex; align-items: center; gap: 4px; font-size: 0.8125rem; color: var(--dp-on-surface-variant); }
.fd-row__location .el-icon { font-size: 12px; }
.fd-row__status { display: inline-flex; align-items: center; gap: 5px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
.fd-row__status i { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.fd-row__status--active { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.fd-row__status--inactive { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); }

.fd-row__specs { display: flex; gap: 28px; flex: 1.5 1 300px; min-width: 0; }
.fd-row__spec { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.fd-row__spec-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dp-outline); }
.fd-row__spec-value { font-size: 0.875rem; font-weight: 600; color: var(--dp-on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.fd-row__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.fd-contact-btn {
    width: 38px; height: 38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    border-radius: 10px; border: 1px solid var(--dp-outline-variant); background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface); text-decoration: none; transition: background .15s ease;
}
.fd-contact-btn:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.fd-contact-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .fd-row { flex-wrap: wrap; }
    .fd-row__specs { order: 3; flex-basis: 100%; padding-top: 12px; margin-top: 4px; border-top: 1px solid var(--dp-outline-variant); }
    .fd-row__actions { order: 2; margin-left: auto; }
}
@media (max-width: 560px) {
    .fd-row { flex-direction: column; align-items: flex-start; gap: 14px; }
    .fd-row__identity { flex: none; width: 100%; }
    .fd-row__actions { order: 3; width: 100%; flex: none; }
    .fd-row__actions .fd-btn-outline { flex: 1; justify-content: center; }
    .fd-row__specs { flex: none; width: 100%; flex-direction: column; gap: 10px; order: 2; }
}

/* ── Reduced motion ────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .fd-row { transition: none; }
    .fd-row:hover { transform: none; }
}
</style>
