<script setup>
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

/* Design-system preview — built strictly to the token values in UI.md
   (dark, compact, developer-console aesthetic). Scoped locally to this
   page (literal hex, not the app's --dp-* light theme) since UI.md
   describes a distinct visual system, same pattern this app already
   uses for other literal-hex "themed" pages. */

const metrics = [
    { label: 'Total Batches', value: '1,284', secondary: '+42 this week' },
    { label: 'Active Lots', value: '317', secondary: '18 pending review' },
    { label: 'Avg Cup Score', value: '86.4', secondary: '+0.6 vs last month' },
    { label: 'On-Chain Records', value: '9,102', secondary: '100% verified' },
];

const batches = [
    { id: 'UG-RBT-2026-0041', variety: 'Robusta', weight: '1,240 kg', status: 'Processing', type: 'warning' },
    { id: 'ET-ARB-2026-0118', variety: 'Arabica', weight: '860 kg', status: 'Verified', type: 'success' },
    { id: 'BR-NAT-2026-0077', variety: 'Natural', weight: '2,100 kg', status: 'Flagged', type: 'error' },
    { id: 'VN-RBT-2026-0033', variety: 'Robusta', weight: '640 kg', status: 'Queued', type: 'info' },
];

const stages = ['Farm', 'Collection', 'Batch', 'Processing', 'Lot', 'Inventory', 'Blockchain', 'Market'];
const activeStageIndex = 4;

const activity = [
    { time: '12:41:08', event: 'BATCH_CREATED', meta: 'UG-RBT-2026-0041' },
    { time: '12:43:02', event: 'QUALITY_RECORDED', meta: 'Moisture: 11.8%' },
    { time: '12:45:03', event: 'LOT_CREATED', meta: 'LOT-2026-0081' },
];
</script>

<template>
<DesignPreviewLayout title="Design Preview">
    <div class="dpv-page">

        <header class="dpv-header">
            <h1 class="dpv-title">Design Preview</h1>
            <p class="dpv-subtitle">Token reference built to UI.md &mdash; dark, compact, developer-console aesthetic.</p>
        </header>

        <section class="dpv-section">
            <div class="dpv-metric-grid">
                <div v-for="m in metrics" :key="m.label" class="dpv-metric">
                    <span class="dpv-metric__label">{{ m.label }}</span>
                    <span class="dpv-metric__value dpv-mono">{{ m.value }}</span>
                    <span class="dpv-metric__secondary">{{ m.secondary }}</span>
                </div>
            </div>
        </section>

        <section class="dpv-section">
            <h2 class="dpv-section-title">Recent Batches</h2>
            <div class="dpv-panel dpv-panel--table">
                <table class="dpv-table">
                    <thead>
                        <tr>
                            <th>Batch ID</th>
                            <th>Variety</th>
                            <th>Weight</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in batches" :key="row.id">
                            <td class="dpv-mono">{{ row.id }}</td>
                            <td>{{ row.variety }}</td>
                            <td class="dpv-mono">{{ row.weight }}</td>
                            <td>
                                <span class="dpv-badge" :class="`dpv-badge--${row.type}`">
                                    <span class="dpv-badge__dot" />{{ row.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dpv-section">
            <h2 class="dpv-section-title">Traceability</h2>
            <div class="dpv-panel">
                <ol class="dpv-timeline">
                    <li v-for="(stage, i) in stages" :key="stage" class="dpv-timeline__item" :class="{ 'dpv-timeline__item--done': i <= activeStageIndex }">
                        <span class="dpv-timeline__node" />
                        <span class="dpv-timeline__title">{{ stage }}</span>
                    </li>
                </ol>
            </div>
        </section>

        <section class="dpv-section">
            <h2 class="dpv-section-title">System Log</h2>
            <div class="dpv-panel dpv-panel--dense">
                <div v-for="row in activity" :key="row.time + row.event" class="dpv-log-row">
                    <span class="dpv-log-row__time dpv-mono">{{ row.time }}</span>
                    <span class="dpv-log-row__event">{{ row.event }}</span>
                    <span class="dpv-log-row__meta dpv-mono">{{ row.meta }}</span>
                </div>
            </div>
        </section>

        <section class="dpv-section">
            <h2 class="dpv-section-title">Controls</h2>
            <div class="dpv-panel dpv-panel--large">
                <div class="dpv-controls-row">
                    <button type="button" class="dpv-btn dpv-btn--primary">Create Batch</button>
                    <button type="button" class="dpv-btn dpv-btn--secondary">Export</button>
                    <button type="button" class="dpv-btn dpv-btn--small">Filter</button>
                    <button type="button" class="dpv-icon-btn" aria-label="More options">&hellip;</button>
                </div>
                <div class="dpv-field">
                    <label class="dpv-field__label" for="dpv-search">Search batches</label>
                    <input id="dpv-search" type="text" class="dpv-input" placeholder="LOT-2026-0081">
                </div>
            </div>
        </section>

    </div>
</DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens (UI.md §1) ─────────────────────────────────────────────────── */
.dpv-page {
    --surface: #ffffff;
    --surface-2: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #292D2E;
    --card-border: #E5E7EB;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --success: #7EE787;
    --warning: #D29922;
    --error: #F85149;
    --info: #58A6FF;
    /* Primary/brand accent (buttons, focus rings) — distinct from
       --info, which stays blue for genuine status semantics only. */
    --primary: #000000;
    --font-sans: Inter, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', monospace;

    /* Offsets DesignPreviewLayout's .dp-main padding (48px 64px desktop,
       stepping down at its own breakpoints below) so the visible margin
       around this page's content is a generous, centered-looking 48px
       on every side (UI.md's own "5xl" spacing token), without touching
       the shared layout used by every other page. */
    margin: 0 -16px;
    color: var(--text);
    font-family: var(--font-sans);
}
.dpv-mono { font-family: var(--font-mono); }

/* ── Page header (§3) ──────────────────────────────────────────────────── */
.dpv-header { margin-bottom: 20px; }
.dpv-title { font-size: 28px; line-height: 36px; font-weight: 600; letter-spacing: -0.02em; color: var(--text); margin: 0 0 6px; }
.dpv-subtitle { font-size: 14px; line-height: 20px; font-weight: 400; color: var(--text-muted); margin: 0; }

/* ── Sections (§3: 32px between sections) ─────────────────────────────── */
.dpv-section + .dpv-section { margin-top: 24px; }
.dpv-section-title { font-size: 18px; line-height: 24px; font-weight: 600; color: var(--text); margin: 0 0 12px; }

/* ── Panels/cards (§7) ─────────────────────────────────────────────────── */
.dpv-panel { background: var(--surface); border: 1px solid var(--card-border); border-radius: 6px; padding: 16px; }
.dpv-panel--dense { padding: 12px; }
.dpv-panel--large { padding: 24px; }
.dpv-panel--table { padding: 0; overflow: hidden; }

/* ── Metric grid (§8, §9) ──────────────────────────────────────────────── */
.dpv-metric-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.dpv-metric { background: var(--surface); border: 1px solid var(--card-border); border-radius: 6px; padding: 16px; display: flex; flex-direction: column; gap: 8px; }
.dpv-metric__label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
.dpv-metric__value { font-size: 24px; line-height: 32px; font-weight: 600; color: var(--text); }
.dpv-metric__secondary { font-size: 12px; line-height: 16px; color: var(--text-2); }

/* ── Table (§10) ───────────────────────────────────────────────────────── */
.dpv-table { width: 100%; border-collapse: collapse; }
.dpv-table thead th {
    height: 40px;
    padding: 10px 12px;
    font-size: 12px;
    font-weight: 600;
    text-align: left;
    color: var(--text-muted);
    background: var(--surface-2);
    border-bottom: 1px solid var(--card-border);
}
.dpv-table tbody td {
    min-height: 44px;
    padding: 10px 12px;
    font-size: 13px;
    color: var(--text);
    border-bottom: 1px solid var(--card-border);
}
.dpv-table tbody tr:last-child td { border-bottom: none; }

/* ── Badges (§17, §16 — color used only for dot/border, not a fill) ──── */
.dpv-badge {
    display: inline-flex; align-items: center; gap: 6px;
    height: 22px; padding: 0 7px; border-radius: 4px;
    font-size: 11px; font-weight: 500;
    background: var(--surface-elevated); color: var(--text-2);
    border: 1px solid var(--card-border);
}
.dpv-badge__dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.dpv-badge--success .dpv-badge__dot { background: var(--success); }
.dpv-badge--warning .dpv-badge__dot { background: var(--warning); }
.dpv-badge--error .dpv-badge__dot { background: var(--error); }
.dpv-badge--info .dpv-badge__dot { background: var(--info); }

/* ── Traceability timeline (§15) ───────────────────────────────────────── */
.dpv-timeline { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; flex-wrap: wrap; gap: 32px; }
.dpv-timeline__item { display: flex; align-items: center; gap: 8px; }
.dpv-timeline__node { width: 8px; height: 8px; border-radius: 50%; background: var(--border); flex-shrink: 0; }
.dpv-timeline__item--done .dpv-timeline__node { background: var(--info); }
.dpv-timeline__title { font-size: 13px; color: var(--text-2); }
.dpv-timeline__item--done .dpv-timeline__title { color: var(--text); }

/* ── Activity / system log (§14) ───────────────────────────────────────── */
.dpv-log-row { display: flex; align-items: baseline; gap: 12px; min-height: 32px; }
.dpv-log-row + .dpv-log-row { margin-top: 4px; }
.dpv-log-row__time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; width: 72px; }
.dpv-log-row__event { font-size: 13px; color: var(--text); flex-shrink: 0; }
.dpv-log-row__meta { font-size: 12px; color: var(--text-2); }

/* ── Buttons (§11) ─────────────────────────────────────────────────────── */
.dpv-controls-row { display: flex; align-items: center; gap: 8px; }
.dpv-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 14px; border-radius: 6px;
    font-family: inherit; font-size: 13px; font-weight: 500;
    border: 1px solid transparent; cursor: pointer;
    transition: background 180ms, border-color 180ms;
}
.dpv-btn--primary { background: var(--primary); color: #ffffff; }
.dpv-btn--primary:hover { background: #262626; }
.dpv-btn--secondary { background: var(--surface-elevated); color: var(--text); border-color: var(--card-border); }
.dpv-btn--secondary:hover { border-color: var(--text-muted); }
.dpv-btn--small { height: 30px; padding: 0 10px; font-size: 12px; background: var(--surface-elevated); color: var(--text-2); border-color: var(--card-border); }
.dpv-icon-btn {
    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-elevated); color: var(--text-2); border: 1px solid var(--card-border); border-radius: 6px;
    cursor: pointer; transition: border-color 180ms;
}
.dpv-icon-btn:hover { border-color: var(--text-muted); }

/* ── Inputs (§12) ──────────────────────────────────────────────────────── */
.dpv-field { margin-top: 16px; max-width: 320px; }
.dpv-field__label { display: block; font-size: 12px; color: var(--text-2); margin-bottom: 6px; }
.dpv-input {
    width: 100%; height: 36px; padding: 0 10px; font-family: inherit; font-size: 13px;
    background: var(--surface-2); color: var(--text); border: 1px solid var(--card-border); border-radius: 6px;
}
.dpv-input::placeholder { color: var(--text-muted); font-family: var(--font-mono); }
.dpv-input:focus { outline: none; border-color: var(--primary); }

/* ── Responsive (§19) ──────────────────────────────────────────────────── */
/* Margins re-tuned to each of .dp-main's own padding breakpoints
   (32px 24px / 24px 16px / 20px 12px) so the visible margin stays
   exactly 48px on every side at every size. */
@media (max-width: 1279.98px) {
    .dpv-page { margin: 16px 24px; }
}
@media (max-width: 1199.98px) and (min-width: 768px) {
    .dpv-metric-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 767.98px) {
    .dpv-page { margin: 24px 32px; }
    .dpv-metric-grid { grid-template-columns: 1fr; }
    .dpv-timeline { gap: 16px; }
    .dpv-controls-row { flex-wrap: wrap; }
}
@media (max-width: 479.98px) {
    .dpv-page { margin: 28px 36px; }
}
</style>
