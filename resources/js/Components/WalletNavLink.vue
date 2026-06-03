<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    variant: { type: String, default: 'aside' },
});
</script>

<template>
    <Link
        :href="route('wallet.index')"
        :class="variant === 'aside'
            ? ['wallet-aside', { active: route().current('wallet.*') }]
            : 'wallet-btn'"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M2 12h20"/>
            <circle cx="16" cy="16" r="1.25" fill="currentColor" stroke="none"/>
        </svg>
        <span class="wallet-text">Wallet</span>
    </Link>
</template>

<style>
.wallet-aside {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    padding: 7px 12px;
    font-size: 13px;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s ease;
    margin-bottom: 2px;
}
.wallet-aside svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}
.wallet-aside .wallet-text {
    flex: 1 1 auto;
    min-width: 0;
}
.wallet-aside:hover,
.wallet-aside.active {
    background: #fff8f0;
    color: #c8862a;
}
.wallet-aside.active {
    font-weight: 500;
}

.wallet-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    border-radius: 0.5rem;
    padding: 0.425rem 0.75rem;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
}
.wallet-btn:hover {
    background: #fff8f0;
    border-color: rgba(200,134,42,.3);
    color: #c8862a;
}
.wallet-btn svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}
</style>
