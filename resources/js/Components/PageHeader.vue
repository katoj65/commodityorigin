<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <section class="ph-header">
        <div class="ph-header__inner">
            <div class="ph-header__content">
                <div v-if="$slots.icon" class="ph-header__icon">
                    <slot name="icon" />
                </div>

                <slot>
                    <div class="ph-header__text">
                        <h1 v-if="title" class="ph-header__title">{{ title }}</h1>
                        <p v-if="subtitle" class="ph-header__subtitle">{{ subtitle }}</p>
                    </div>
                </slot>
            </div>

            <div v-if="$slots.actions" class="ph-header__actions">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>

<style scoped>
/*
 * Standard compact page header — used across internal pages so every
 * title bar shares the same height, typography, and layout structure
 * regardless of which page embeds it. Tokens are local to this
 * component (not inherited from a host page's own CSS variables) so it
 * renders identically everywhere.
 */
.ph-header {
    --ph-green: #145c42;
    --ph-border: #eef2f0;
    --ph-on-surface: #111827;
    --ph-on-surface-var: #6b7280;
    --ph-surface-low: #f8fafc;
    background: #fff;
    border-bottom: 1px solid var(--ph-border);
    font-family: 'Manrope', system-ui, sans-serif;
}

.ph-header__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
}

.ph-header__content {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.ph-header__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--ph-surface-low);
    color: var(--ph-green);
    overflow: hidden;
    flex-shrink: 0;
}
.ph-header__icon :deep(img) { width: 100%; height: 100%; object-fit: cover; }

.ph-header__text { min-width: 0; }
.ph-header__title {
    font-size: 1.0625rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--ph-on-surface);
    margin: 0;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ph-header__subtitle {
    font-size: 0.75rem;
    color: var(--ph-on-surface-var);
    margin: 1px 0 0;
}

.ph-header__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
    max-width: 100%;
}

@media (max-width: 640px) {
    .ph-header__inner { padding: 0.875rem 1.25rem; }
}
</style>
