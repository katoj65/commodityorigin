<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ChatDotRound, Checked, CollectionTag, DataLine,
    Medal, Setting, ShoppingCart, TrendCharts, UserFilled, Van, Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    agents: { type: Array, default: () => [] },
    subscribedAgentIds: { type: Array, default: () => [] },
});

const ICONS = {
    buyer_agent: ShoppingCart,
    quality_grading_agent: Medal,
    price_recommendation_agent: TrendCharts,
    compliance_verification_agent: Checked,
    market_matching_agent: CollectionTag,
    bid_negotiation_agent: ChatDotRound,
    harvest_forecasting_agent: DataLine,
    fraud_detection_agent: Warning,
    logistics_agent: Van,
    onboarding_agent: UserFilled,
};

const agentIcon = (type) => ICONS[type] ?? Setting;

const statusLabel = (s) => ({
    pending: 'Pending',
    active: 'Active',
    success: 'Success',
    failed: 'Failed',
}[s] ?? s ?? '—');

const statusCls = (s) => ({
    pending: 'ap-badge--yellow',
    active: 'ap-badge--green',
    success: 'ap-badge--green',
    failed: 'ap-badge--red',
}[s] ?? 'ap-badge--muted');

const subscribing = ref(null);

const isSubscribed = (agentId) => props.subscribedAgentIds.includes(agentId);

function toggleSubscription(agent) {
    subscribing.value = agent.id;
    const options = {
        preserveScroll: true,
        onFinish: () => { subscribing.value = null; },
    };

    if (isSubscribed(agent.id)) {
        router.delete(route('agent.unsubscribe', agent.id), options);
    } else {
        router.post(route('agent.subscribe', agent.id), {}, options);
    }
}
</script>

<template>
    <AppLayout title="Apps" full-width flush :show-banner="false">
        <Head title="Apps" />

        <div class="ap-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="ap-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="py-3">
                        <div class="ap-kicker">Automation</div>
                        <h1 class="ap-title mb-0">Apps</h1>
                        <p class="ap-subtitle mb-0">Browse available AI agents and add the ones you want to your subscription.</p>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── Empty state ─────────────────────────────────────── -->
                <div v-if="!props.agents.length" class="ap-empty">
                    <el-icon style="font-size:2rem;color:#d1d5db;"><Setting /></el-icon>
                    <p class="ap-muted mt-2 mb-0">No agents are available yet.</p>
                </div>

                <!-- ── Tile grid ───────────────────────────────────────── -->
                <div v-else class="row g-3">
                    <div v-for="agent in props.agents" :key="agent.id" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="ap-tile h-100">
                            <div class="ap-tile__icon">
                                <el-icon><component :is="agentIcon(agent.agent_type)" /></el-icon>
                            </div>
                            <div class="ap-tile__name">{{ agent.name }}</div>
                            <p class="ap-tile__desc">{{ agent.description }}</p>
                            <div class="ap-tile__footer mt-3">
                                <!-- <span class="ap-badge" :class="statusCls(agent.status)">{{ statusLabel(agent.status) }}</span> -->
                                <button
                                    type="button"
                                    class="btn btn-sm w-100"
                                    :class="isSubscribed(agent.id) ? 'ap-btn-outline' : 'ap-btn-primary'"
                                    :disabled="subscribing === agent.id"
                                    @click="toggleSubscription(agent)"
                                >
                                    {{
                                        subscribing === agent.id
                                            ? (isSubscribed(agent.id) ? 'Unsubscribing…' : 'Subscribing…')
                                            : (isSubscribed(agent.id) ? 'Unsubscribe' : 'Add to Subscription')
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.ap-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}
.ap-muted { color: var(--on-surface-var); }

.ap-header   { background: #fff; border-bottom: 1px solid var(--border); }
.ap-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.ap-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.ap-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.ap-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 48px 20px; background: var(--surface-low); border: 1px solid var(--border); border-radius: 10px; text-align: center; }

.ap-tile { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .15s, transform .15s; }
.ap-tile:hover { box-shadow: 0 8px 24px rgba(0,0,0,.10); transform: translateY(-2px); border-color: rgba(0,69,50,0.2); }
.ap-tile__icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
.ap-tile__name { font-size: .9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; }
.ap-tile__desc { font-size: .8125rem; color: var(--on-surface-var); line-height: 1.55; flex: 1; margin-bottom: 20px; }
.ap-tile__footer { display: flex; flex-direction: column; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }

.ap-badge { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 8px; }
.ap-badge--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.ap-badge--yellow { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.ap-badge--red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.ap-badge--muted  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

.ap-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.ap-btn-primary:hover { background: #065f46; }
.ap-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.ap-btn-outline:hover { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
</style>
