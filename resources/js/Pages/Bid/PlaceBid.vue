<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Clock,
    DataAnalysis,
    Files,
    Location,
    Medal,
    Money,
    Promotion,
    Trophy,
    User,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
});

const form = useForm({
    lot_id:     props.lot?.id ?? '',
    bid_amount: '',
    quantity:   '',
    notes:      '',
});

const lotCode      = computed(() => props.lot?.lot_code      || 'LOT-2026-001');
const lotName      = computed(() => props.lot?.lot_name      || 'Bugisu Premium Export Lot');
const origin       = computed(() => props.lot?.origin        || 'Mbale, Uganda');
const process      = computed(() => props.lot?.process       || 'Washed');
const qualityScore = computed(() => props.lot?.quality_score || 87.5);
const grade        = computed(() => props.lot?.grade         || 'AA');
const pricePerKg   = computed(() => Number(props.lot?.price_per_kg || 12.50));
const netWeight    = computed(() => Number(props.lot?.net_weight_kg || 1200));

const minBid = computed(() => (pricePerKg.value * 0.95).toFixed(2));
const bidTotal = computed(() => {
    const qty = Number(form.quantity) || 0;
    const amt = Number(form.bid_amount) || 0;
    return (qty * amt).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

/* ── Client-side validation ─────────────────────────────────────────────── */
const clientErrors = ref({});

const validate = () => {
    const errors = {};
    const amount = Number(form.bid_amount);
    const qty    = Number(form.quantity);

    if (!form.bid_amount) {
        errors.bid_amount = 'Bid price is required.';
    } else if (isNaN(amount) || amount <= 0) {
        errors.bid_amount = 'Enter a valid bid price.';
    } else if (amount < Number(minBid.value)) {
        errors.bid_amount = `Minimum bid is Shs. ${minBid.value} / kg.`;
    }

    if (!form.quantity) {
        errors.quantity = 'Quantity is required.';
    } else if (isNaN(qty) || qty <= 0) {
        errors.quantity = 'Enter a valid quantity.';
    } else if (qty > netWeight.value) {
        errors.quantity = `Maximum available quantity is ${netWeight.value.toLocaleString()} kg.`;
    }

    clientErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const submit = () => {
    if (!validate()) return;
    form.post(route('bid.store'), {
        onSuccess: () => router.visit(route('bid.status')),
    });
};
</script>

<template>
    <AppLayout title="Place Bid" full-width flush :show-banner="false">
        <div class="pb-root">

            <!-- Page header -->
            <div class="pb-header">
                <Link :href="route('bid.index')" class="pb-back">
                    <el-icon><ArrowLeft /></el-icon> Back to Bids
                </Link>
                <div class="pb-header__meta">
                    <h1 class="pb-header__title">Place a Bid</h1>
                    <p class="pb-header__sub">Submit your bid for this coffee lot on the open market.</p>
                </div>
            </div>

            <div class="pb-layout">

                <!-- Left: Lot summary -->
                <aside class="pb-aside">
                    <div class="pb-lot-card">
                        <div class="pb-lot-img">
                            <img
                                :src="lot.image || 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn'"
                                :alt="lotName"
                                class="pb-lot-img__photo"
                            />
                        </div>
                        <div class="pb-lot-info">
                            <span class="pb-lot-code">
                                <el-icon><Files /></el-icon> {{ lotCode }}
                            </span>
                            <h2 class="pb-lot-name">{{ lotName }}</h2>
                            <p class="pb-lot-sub">
                                <el-icon><Location /></el-icon> {{ origin }}
                                <span class="pb-lot-sep">&middot;</span>
                                {{ process }}
                            </p>
                        </div>
                        <div class="pb-lot-stats">
                            <div class="pb-stat">
                                <span class="pb-stat__label"><el-icon><Trophy /></el-icon> Cupping Score</span>
                                <strong class="pb-stat__val pb-accent">{{ Number(qualityScore).toFixed(1) }} SCA</strong>
                            </div>
                            <div class="pb-stat">
                                <span class="pb-stat__label"><el-icon><Medal /></el-icon> Grade</span>
                                <strong class="pb-stat__val">{{ grade }}</strong>
                            </div>
                            <div class="pb-stat">
                                <span class="pb-stat__label"><el-icon><Money /></el-icon> Ask Price</span>
                                <strong class="pb-stat__val">Shs. {{ pricePerKg.toFixed(2) }} / kg</strong>
                            </div>
                            <div class="pb-stat">
                                <span class="pb-stat__label"><el-icon><DataAnalysis /></el-icon> Available</span>
                                <strong class="pb-stat__val">{{ netWeight.toLocaleString() }} kg</strong>
                            </div>
                        </div>
                        <div class="pb-lot-note">
                            <el-icon><Clock /></el-icon>
                            Bid closes in <strong>47h 32m</strong>
                        </div>
                    </div>
                </aside>

                <!-- Right: Bid form -->
                <main class="pb-main">
                    <form class="pb-form" @submit.prevent="submit">

                        <div class="pb-form-section">
                            <h3 class="pb-section-title">Bid Details</h3>

                            <div class="pb-field">
                                <label class="pb-label">Bid Price per kg (Shs.)</label>
                                <div class="pb-input-wrap">
                                    <input
                                        v-model="form.bid_amount"
                                        type="number"
                                        step="0.01"
                                        :min="minBid"
                                        class="pb-input"
                                        :class="{ 'pb-input--error': clientErrors.bid_amount || form.errors.bid_amount }"
                                        placeholder="e.g. 12.00"
                                    />
                                </div>
                                <p class="pb-hint">Minimum bid: Shs. {{ minBid }} / kg (5% below ask)</p>
                                <p v-if="clientErrors.bid_amount || form.errors.bid_amount" class="pb-error">{{ clientErrors.bid_amount || form.errors.bid_amount }}</p>
                            </div>

                            <div class="pb-field">
                                <label class="pb-label">Quantity (kg)</label>
                                <div class="pb-input-wrap">
                                    <input
                                        v-model="form.quantity"
                                        type="number"
                                        step="1"
        
                                        class="pb-input"
                                        :class="{ 'pb-input--error': clientErrors.quantity || form.errors.quantity }"
                                        placeholder="Min. 60 kg"
                                    />
                                </div>
                                <p class="pb-hint">Available: {{ netWeight.toLocaleString() }} kg</p>
                                <p v-if="clientErrors.quantity || form.errors.quantity" class="pb-error">{{ clientErrors.quantity || form.errors.quantity }}</p>
                            </div>

                            <div class="pb-field">
                                <label class="pb-label">Notes <span class="pb-optional">(optional)</span></label>
                                <textarea
                                    v-model="form.notes"
                                    class="pb-textarea"
                                    rows="3"
                                    placeholder="Delivery preferences, questions for the seller…"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Bid summary -->
                        <div class="pb-summary">
                            <div class="pb-summary__row">
                                <span>Bid price</span>
                                <strong>Shs. {{ form.bid_amount || '—' }} / kg</strong>
                            </div>
                            <div class="pb-summary__row">
                                <span>Quantity</span>
                                <strong>{{ form.quantity || '—' }} kg</strong>
                            </div>
                            <div class="pb-summary__divider"></div>
                            <div class="pb-summary__row pb-summary__row--total">
                                <span>Estimated Total</span>
                                <strong class="pb-accent">Shs. {{ form.bid_amount && form.quantity ? bidTotal : '—' }}</strong>
                            </div>
                        </div>

                        <div class="pb-form-actions">
                            <Link :href="route('bid.index')" class="pb-btn pb-btn--ghost">Cancel</Link>
                            <button type="submit" class="pb-btn pb-btn--primary" :disabled="form.processing">
                                <el-icon><Promotion /></el-icon>
                                {{ form.processing ? 'Submitting…' : 'Submit Bid' }}
                            </button>
                        </div>

                    </form>
                </main>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.pb-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface:          #f7f9fb;
    --surface-low:      #f2f4f6;
    --surface-high:     #eef2f0;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --outline-variant:  #bec9c2;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
    padding: 0;
}

/* ── Header ───────────────────────────────────────────────────────────────── */
.pb-header {
    padding: 1.5rem 2rem 0;
    margin-bottom: 2rem;
}
.pb-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    margin-bottom: 1.25rem;
}
.pb-back:hover { text-decoration: underline; }
.pb-header__title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
}
.pb-header__sub {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
}

/* ── Layout ───────────────────────────────────────────────────────────────── */
.pb-layout {
    padding: 0 2rem 2rem;
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    align-items: start;
}

/* ── Lot card (aside) ─────────────────────────────────────────────────────── */
.pb-aside { display: grid; gap: 0; }
.pb-lot-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.pb-lot-img { width: 100%; height: 180px; }
.pb-lot-img__photo { width: 100%; height: 100%; object-fit: cover; display: block; }
.pb-lot-info { padding: 1.25rem 1.25rem 0; }
.pb-lot-code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: 6px;
}
.pb-lot-name {
    font-size: 1rem;
    font-weight: 800;
    color: var(--on-surface);
    margin: 0 0 6px;
    letter-spacing: -0.01em;
}
.pb-lot-sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
    margin: 0;
}
.pb-lot-sub .el-icon { font-size: 12px; }
.pb-lot-sep { color: var(--outline-variant); }

.pb-lot-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--surface-high);
    margin-top: 1rem;
}
.pb-stat {
    padding: 8px 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.pb-stat:nth-child(odd) { padding-right: 8px; border-right: 1px solid var(--surface-high); }
.pb-stat:nth-child(even) { padding-left: 8px; }
.pb-stat:nth-child(1),
.pb-stat:nth-child(2) { border-bottom: 1px solid var(--surface-high); }
.pb-stat__label {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--on-surface-var);
}
.pb-stat__label .el-icon { font-size: 11px; }
.pb-stat__val { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }

.pb-lot-note {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    padding: 0.875rem 1.25rem;
    border-top: 1px solid var(--surface-high);
    background: var(--surface-low);
}
.pb-lot-note .el-icon { font-size: 13px; color: var(--primary); }
.pb-lot-note strong { color: var(--on-surface); }

/* ── Form ─────────────────────────────────────────────────────────────────── */
.pb-form {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.pb-form-section { padding: 1.75rem; display: grid; gap: 1.25rem; }
.pb-section-title {
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--on-surface);
    margin: 0;
    letter-spacing: -0.01em;
}
.pb-field { display: grid; gap: 6px; }
.pb-label {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
}
.pb-optional { font-weight: 500; color: var(--on-surface-var); }
.pb-input-wrap { position: relative; }
.pb-input,
.pb-textarea {
    width: 100%;
    background: var(--surface-low);
    border: 1px solid var(--surface-high);
    border-radius: 0.5rem;
    color: var(--on-surface);
    font-size: 0.9375rem;
    font-family: 'Manrope', system-ui, sans-serif;
    padding: 11px 14px;
    outline: none;
    transition: border-color 0.15s ease, background 0.15s ease;
    box-sizing: border-box;
}
.pb-input:focus,
.pb-textarea:focus {
    border-color: var(--primary);
    background: #ffffff;
}
.pb-input--error { border-color: #dc2626; }
.pb-textarea { resize: vertical; min-height: 80px; }
.pb-hint  { font-size: 0.75rem; color: var(--on-surface-var); margin: 0; }
.pb-error { font-size: 0.75rem; color: #dc2626; font-weight: 600; margin: 0; }

/* ── Summary ──────────────────────────────────────────────────────────────── */
.pb-summary {
    border-top: 1px solid var(--surface-high);
    padding: 1.25rem 1.75rem;
    display: grid;
    gap: 10px;
    background: var(--surface-low);
}
.pb-summary__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
}
.pb-summary__row span   { color: var(--on-surface-var); }
.pb-summary__row strong { color: var(--on-surface); font-weight: 700; }
.pb-summary__divider { height: 1px; background: var(--surface-high); margin: 2px 0; }
.pb-summary__row--total { font-size: 1rem; }
.pb-summary__row--total span   { font-weight: 700; color: var(--on-surface); }
.pb-summary__row--total strong { font-size: 1.125rem; font-weight: 800; }

/* ── Actions ──────────────────────────────────────────────────────────────── */
.pb-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 1.25rem 1.75rem;
    border-top: 1px solid var(--surface-high);
    background: #ffffff;
}
.pb-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.5rem;
    padding: 10px 22px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: opacity 0.15s ease, background 0.15s ease;
    border: none;
}
.pb-btn--primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.pb-btn--primary:hover   { opacity: 0.9; }
.pb-btn--primary:disabled { opacity: 0.55; cursor: not-allowed; }
.pb-btn--ghost {
    background: transparent;
    color: var(--on-surface-var);
    border: 1px solid var(--outline-variant);
}
.pb-btn--ghost:hover { background: var(--surface-low); color: var(--on-surface); }

.pb-accent { color: var(--primary) !important; }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 720px) {
    .pb-header { padding: 1rem 1rem 0; }
    .pb-layout { grid-template-columns: 1fr; padding: 0 1rem 2rem; }
    .pb-lot-img { height: 140px; }
}
</style>
