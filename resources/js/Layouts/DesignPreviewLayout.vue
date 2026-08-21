<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Bell, Calendar, Close, Compass, Document, Grid, MagicStick, Menu, Message, Odometer, Search,
    Setting, Shop, ShoppingCart, SwitchButton, Tickets, User, Wallet,
} from '@element-plus/icons-vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    title: { type: String, default: 'Bean Origin' },
});

const mobileNavOpen = ref(false);

// Mirrors AppAside.vue's non-admin nav exactly (same section labels,
// link labels, and icons) so this preview reads as the same product —
// now wired to the same real routes AppAside.vue uses.
const navSections = computed(() => [
    {
        label: 'Main',
        items: [
            { index: 'dashboard', label: 'Dashboard', icon: Grid, href: route('dashboard'), active: route().current('dashboard') },
        ],
    },
    {
        label: 'Marketplace',
        items: [
            { index: 'market', label: 'Market', icon: Compass, href: route('market.index'), active: route().current('market.index') },
            { index: 'store', label: 'My Store', icon: Shop, href: route('store.show'), active: route().current('store.*') },
            { index: 'calendar', label: 'Calendar', icon: Calendar, href: route('calendar.index'), active: route().current('calendar.*') },
        ],
    },
    {
        label: 'Financials',
        items: [
            { index: 'purchases', label: 'Purchases', icon: Tickets, href: route('purchases.index'), active: route().current('purchases.*') },
            { index: 'wallet', label: 'Wallet', icon: Wallet, href: route('wallet.index'), active: route().current('wallet.*') },
        ],
    },
    {
        label: 'Analysis',
        items: [
            { index: 'chat', label: 'AI Assistant', icon: MagicStick, tertiary: true, href: route('chat.index'), active: route().current('chat.*') },
        ],
    },
]);

const activeNavIndex = computed(() => {
    for (const section of navSections.value) {
        const match = section.items.find((item) => item.active);
        if (match) return match.index;
    }
    return '';
});

// "Settings" has no dedicated page in this app yet — points at the
// profile page (the closest real settings surface) until one exists.
const accountMenuItems = [
    { label: 'Profile Settings', icon: User, href: route('profile.show') },
    { label: 'Overview', icon: Odometer, href: route('dashboard') },
    { label: 'Settings', icon: Setting, href: route('profile.show') },
    { label: 'Documentation', icon: Document, href: route('documentation.index') },
];

function signOut() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="dp-shell">
        <Head :title="title">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600;700&display=swap" rel="stylesheet">
        </Head>

        <!-- ── Sidebar ──────────────────────────────────────────────────── -->
        <div v-if="mobileNavOpen" class="dp-scrim" @click="mobileNavOpen = false" />

        <aside class="dp-aside" :class="{ 'dp-aside--open': mobileNavOpen }">
            <div class="dp-aside__brand">
                <span class="dp-aside__mark-wrap">
                    <ApplicationMark class="dp-aside__mark" />
                </span>
                <span class="dp-aside__brand-text">
                    <span class="dp-aside__title">Bean Origin</span>
                    <span class="dp-aside__tagline">Coffee Trade Platform</span>
                </span>
                <el-button text circle class="dp-aside__close" @click="mobileNavOpen = false">
                    <el-icon :size="18"><Close /></el-icon>
                </el-button>
            </div>

            <el-menu :default-active="activeNavIndex" class="dp-nav" @select="mobileNavOpen = false">
                <template v-for="section in navSections" :key="section.label">
                    <div class="dp-nav__label">{{ section.label }}</div>
                    <el-menu-item
                        v-for="item in section.items"
                        :key="item.index"
                        :index="item.index"
                        class="dp-nav__item"
                        :class="{ 'dp-nav__item--tertiary': item.tertiary }"
                    >
                        <Link :href="item.href" class="dp-nav__link">
                            <span class="dp-nav__icon"><el-icon :size="16"><component :is="item.icon" /></el-icon></span>
                            <span>{{ item.label }}</span>
                        </Link>
                    </el-menu-item>
                </template>
            </el-menu>

            <div class="dp-aside__footer">
                <span class="dp-aside__avatar-wrap">
                    <el-avatar :size="34" class="dp-avatar"><el-icon><User /></el-icon></el-avatar>
                    <span class="dp-aside__status" />
                </span>
                <div class="dp-aside__user">
                    <p class="dp-aside__user-name">Roaster Admin</p>
                    <p class="dp-aside__user-role">Bean Origin</p>
                </div>
            </div>
        </aside>

        <div class="dp-content">
            <!-- ── Header ───────────────────────────────────────────────── -->
            <header class="dp-header">
                <el-button text circle class="dp-menu-btn" @click="mobileNavOpen = true">
                    <el-icon :size="20"><Menu /></el-icon>
                </el-button>

                <div class="dp-header__search">
                    <el-input placeholder="Search subscribers, products..." :prefix-icon="Search" class="dp-search-input" />
                </div>

                <div class="dp-header__actions">
                    <el-button class="dp-ask-ai-btn" round @click="router.visit(route('chat.index'))">
                        <el-icon :size="16"><MagicStick /></el-icon>
                        <span class="dp-ask-ai-btn__label">Ask AI</span>
                    </el-button>
                    <el-badge :value="3" class="dp-icon-badge">
                        <el-button text circle class="dp-icon-btn" @click="router.visit(route('notifications.index'))">
                            <el-icon :size="20"><Bell /></el-icon>
                        </el-button>
                    </el-badge>
                    <el-badge :value="2" class="dp-icon-badge">
                        <el-button text circle class="dp-icon-btn">
                            <el-icon :size="20"><Message /></el-icon>
                        </el-button>
                    </el-badge>
                    <el-badge :value="5" class="dp-icon-badge">
                        <el-button text circle class="dp-icon-btn" @click="router.visit(route('checkout.index'))">
                            <el-icon :size="20"><ShoppingCart /></el-icon>
                        </el-button>
                    </el-badge>
                    <el-dropdown trigger="click" placement="bottom-end" popper-class="dp-account-menu">
                        <div class="dp-account">
                            <el-avatar :size="32" class="dp-avatar"><el-icon><User /></el-icon></el-avatar>
                        </div>

                        <template #dropdown>
                            <div class="dp-account-menu__head">
                                <p class="dp-label-md">Roaster Admin</p>
                                <p class="dp-caption">Bean Origin</p>
                            </div>
                            <el-dropdown-menu>
                                <el-dropdown-item
                                    v-for="item in accountMenuItems"
                                    :key="item.label"
                                    :icon="item.icon"
                                    @click="router.visit(item.href)"
                                >
                                    {{ item.label }}
                                </el-dropdown-item>
                                <el-dropdown-item divided :icon="SwitchButton" @click="signOut">Sign Out</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </header>

            <main class="dp-main">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* ── Design tokens — scoped to this preview only via CSS custom
   properties, not tailwind.config.js: this app's shared config
   already defines an old dark-theme palette (bg, surface, green,
   gold, cream…) still consumed by Login/Register/OuterLayout, and
   several of this mockup's token NAMES collide (e.g. "surface") with
   different hex values there. Plain scoped CSS also sidesteps a real
   bug found while building this page: the local Vite dev server
   double-escapes parentheses inside Tailwind arbitrary values (e.g.
   bg-[var(--x)]), silently producing invalid selectors — using real
   CSS var() in a normal stylesheet has no such issue. ─────────────── */
.dp-shell {
    --dp-surface: #f9f9f9;
    --dp-surface-container-lowest: #ffffff;
    --dp-surface-container-low: #f3f3f3;
    --dp-surface-container: #eeeeee;
    --dp-surface-container-high: #e8e8e8;
    --dp-surface-container-highest: #e2e2e2;
    --dp-on-surface: #1a1c1c;
    --dp-on-surface-variant: #504442;
    --dp-outline: #827472;
    --dp-outline-variant: #d3c3c0;
    --dp-surface-tint: #745853;
    --dp-primary: #271310;
    --dp-on-primary: #ffffff;
    --dp-primary-container: #3e2723;
    --dp-on-primary-container: #ae8d87;
    --dp-primary-fixed: #ffdad4;
    --dp-on-primary-fixed: #2b1613;
    --dp-secondary: #1b6d24;
    --dp-secondary-container: #a0f399;
    --dp-on-secondary-container: #217128;
    --dp-tertiary-fixed: #e6e1e1;
    --dp-on-tertiary-fixed: #1d1b1b;
    --dp-error: #ba1a1a;
    --dp-on-error: #ffffff;
    --dp-on-error-container: #93000a;
    --dp-error-container: #ffdad6;
    --dp-border-subtle: rgba(62, 39, 35, 0.08);

    --dp-font-sans: 'Manrope', system-ui, -apple-system, sans-serif;
    --dp-font-mono: 'IBM Plex Mono', ui-monospace, monospace;

    display: flex;
    min-height: 100vh;
    background: var(--dp-surface);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

/* ── Typography tokens — one professional sans-serif family (Manrope,
   this app's actual established headline/body font — see e.g.
   ProductProfile.vue, StorePage.vue) instead of a display serif, which
   reads as editorial/magazine rather than business software. Numeric
   values use dp-mono (IBM Plex Mono) separately, matching this app's
   convention of monospacing prices/stats for tabular alignment. ───── */
.dp-shell .dp-display-lg { font-size: 26px; line-height: 33px; letter-spacing: -0.012em; font-weight: 800; margin: 0; }
.dp-shell .dp-display-md { font-size: 24px; line-height: 31px; letter-spacing: -0.012em; font-weight: 800; margin: 0; }
.dp-shell .dp-headline-md { font-size: 17px; line-height: 25px; letter-spacing: -0.006em; font-weight: 700; margin: 0; }
.dp-shell .dp-headline-sm { font-size: 14px; line-height: 21px; letter-spacing: -0.003em; font-weight: 700; margin: 0; }
.dp-shell .dp-body-lg { font-size: 13.5px; line-height: 21px; font-weight: 500; letter-spacing: 0.001em; margin: 0; }
.dp-shell .dp-body-md { font-size: 13px; line-height: 20px; font-weight: 500; letter-spacing: 0.002em; margin: 0; }
.dp-shell .dp-label-md { font-size: 11.5px; line-height: 16px; letter-spacing: 0.05em; font-weight: 700; margin: 0; }
.dp-shell .dp-caption { font-size: 11px; line-height: 16px; font-weight: 600; letter-spacing: 0.008em; margin: 0; }
.dp-shell .dp-mono { font-family: var(--dp-font-mono); font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }

/* ── Sidebar ──────────────────────────────────────────────────────────── */
.dp-aside {
    width: 288px;
    flex-shrink: 0;
    background: var(--dp-surface-container-low);
    border-right: 1px solid var(--dp-border-subtle);
    display: flex;
    flex-direction: column;
    padding-top: 28px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

/* ── Brand — mirrors the real AppAside.vue brand block (same logo mark,
   title, and tagline) so this preview reads as the same product. ──── */
.dp-aside__brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    margin-bottom: 36px;
}
.dp-aside__close.el-button { display: none; margin-left: auto; flex-shrink: 0; color: var(--dp-on-surface-variant); }
.dp-aside__mark-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--dp-surface-container-lowest);
    box-shadow: 0 0 0 1px var(--dp-border-subtle), 0 2px 6px rgba(39, 19, 16, 0.12);
    overflow: hidden;
}
.dp-aside__mark { height: 30px; width: 30px; flex-shrink: 0; }
.dp-aside__brand-text { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.dp-aside__title {
    font-size: 19px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: var(--dp-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dp-aside__tagline {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--dp-outline);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dp-nav__label {
    padding: 0 12px;
    margin: 20px 0 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--dp-outline);
}
.dp-nav__label:first-child { margin-top: 0; }

.dp-nav.el-menu {
    flex: 1;
    border-right: none;
    background: transparent;
    padding: 0 16px;
}
.dp-nav .dp-nav__item.el-menu-item {
    height: auto;
    line-height: normal;
    padding: 0 !important;
    margin-bottom: 4px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: var(--dp-on-surface-variant);
    transition: all 0.2s ease;
}
.dp-nav__link {
    display: flex;
    align-items: center;
    width: 100%;
    height: 100%;
    padding: 10px 12px;
    gap: 12px;
    color: inherit;
    text-decoration: none;
}
.dp-nav .dp-nav__item.el-menu-item:hover {
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface);
}
.dp-nav .dp-nav__item.el-menu-item.is-active {
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    font-weight: 700;
}
.dp-nav .dp-nav__item .el-icon { color: inherit; }

.dp-nav__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 8px;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface-variant);
    transition: background 0.2s ease, color 0.2s ease;
}
.dp-nav .dp-nav__item.el-menu-item:hover .dp-nav__icon {
    background: var(--dp-surface-container-highest);
    color: var(--dp-on-surface);
}
.dp-nav .dp-nav__item.el-menu-item.is-active .dp-nav__icon {
    background: var(--dp-primary);
    color: var(--dp-on-primary);
}

/* AI Assistant — matches AppAside.vue's tertiary nav-item treatment
   (literal hex: this brand-tertiary plum isn't among the dp-* tokens). */
.dp-nav .dp-nav__item--tertiary.el-menu-item { color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item:hover { background: rgba(146, 51, 87, 0.08); color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item:hover .dp-nav__icon { background: rgba(146, 51, 87, 0.14); color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item.is-active { background: #923357; color: #ffffff; }
.dp-nav .dp-nav__item--tertiary.el-menu-item.is-active .dp-nav__icon { background: rgba(255, 255, 255, 0.2); color: #ffffff; }

.dp-aside__footer {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 20px 0;
    padding: 12px;
    border-radius: 14px;
    background: var(--dp-surface-container);
}
.dp-aside__avatar-wrap { position: relative; flex-shrink: 0; }
.dp-aside__status {
    position: absolute;
    right: -1px;
    bottom: -1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--dp-secondary);
    border: 2px solid var(--dp-surface-container);
}
.dp-aside__user { min-width: 0; overflow: hidden; }
.dp-aside__user-name { font-size: 14px; font-weight: 600; color: var(--dp-on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
.dp-aside__user-role { font-size: 12px; color: var(--dp-on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }

/* ── Content column ───────────────────────────────────────────────────── */
.dp-content { flex: 1; min-width: 0; }

.dp-header {
    position: sticky;
    top: 0;
    z-index: 10;
    height: 80px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 24px;
    background: rgba(249, 249, 249, 0.9);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--dp-border-subtle);
}
.dp-menu-btn.el-button { display: none; flex-shrink: 0; color: var(--dp-on-surface-variant); font-size: 20px; }
.dp-header__search { flex: 1; display: flex; justify-content: center; min-width: 0; }
.dp-search-input { max-width: 576px; width: 100%; }
.dp-search-input .el-input__wrapper {
    background: var(--dp-surface-container-low);
    border-radius: 10px;
    box-shadow: none;
    height: 44px;
    padding-left: 16px;
}
.dp-search-input .el-input__wrapper.is-focus { box-shadow: 0 0 0 2px var(--dp-primary-container) inset; }
.dp-search-input :deep(.el-input__inner) { font-size: 14px; }

.dp-header__actions { display: flex; align-items: center; gap: 20px; margin-left: auto; padding-left: 24px; }

.dp-ask-ai-btn.el-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 18px;
    margin-right: 4px;
    border: none;
    background: var(--dp-primary);
    color: var(--dp-on-primary);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.dp-ask-ai-btn.el-button:hover {
    background: var(--dp-primary);
    color: var(--dp-on-primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 19, 16, 0.24);
}
.dp-ask-ai-btn.el-button .dp-ask-ai-btn__label { font-family: var(--dp-font-sans); }
.dp-icon-btn { color: var(--dp-on-surface-variant); }
.dp-icon-btn:hover { color: var(--dp-on-surface); background: var(--dp-surface-container); }
.dp-icon-badge :deep(.el-badge__content) { background: var(--dp-error); border: 1.5px solid var(--dp-surface); font-family: var(--dp-font-mono); font-size: 10px; height: 16px; line-height: 16px; padding: 0 4px; }

.dp-avatar { background: var(--dp-primary); color: var(--dp-on-primary); flex-shrink: 0; }

.dp-account { display: flex; align-items: center; cursor: pointer; border-radius: 999px; transition: box-shadow 0.15s ease; }
.dp-account:hover { box-shadow: 0 0 0 3px #eeeeee; }

/* The dropdown popper teleports to <body>, outside .dp-shell, so its
   --dp-* custom properties (defined on .dp-shell) don't cascade in —
   literal hex from the same palette is used here instead. */
.dp-account-menu.el-dropdown__popper {
    font-family: 'Manrope', system-ui, sans-serif;
    border-radius: 10px;
    border: 1px solid #e2e2e2;
    box-shadow: 0 12px 32px rgba(39, 19, 16, 0.16);
    min-width: 220px;
}
.dp-account-menu .el-dropdown-menu { padding: 6px; }
.dp-account-menu__head { padding: 12px 14px 10px; border-bottom: 1px solid #eeeeee; margin-bottom: 4px; }
.dp-account-menu__head p:first-child { color: #1a1c1c; margin: 0 0 2px; }
.dp-account-menu__head p:last-child { color: #827472; margin: 0; }
.dp-account-menu .el-dropdown-menu__item {
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #504442;
    padding: 9px 10px;
    gap: 10px;
}
.dp-account-menu .el-dropdown-menu__item:hover { background: #f3f3f3; color: #1a1c1c; }
.dp-account-menu .el-dropdown-menu__item.is-divided { border-top-color: #eeeeee; }
.dp-account-menu .el-dropdown-menu__item.is-divided:hover { color: #ba1a1a; background: #ffdad6; }

.dp-main { padding: 48px 64px; display: flex; flex-direction: column; gap: 32px; }

.dp-scrim {
    position: fixed;
    inset: 0;
    z-index: 35;
    background: rgba(26, 20, 18, 0.45);
    animation: dp-scrim-in 0.2s ease;
}
@keyframes dp-scrim-in { from { opacity: 0; } to { opacity: 1; } }

/* ── Tablet & mobile — sidebar becomes an off-canvas drawer instead of
   disappearing entirely, so nav stays reachable below the desktop
   breakpoint. Opened via the header's hamburger button. ────────────── */
@media (max-width: 1279.98px) {
    .dp-aside {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 40;
        height: 100dvh;
        transform: translateX(-100%);
        box-shadow: 0 16px 40px rgba(39, 19, 16, 0.24);
        transition: transform 0.25s ease;
    }
    .dp-aside--open { transform: translateX(0); }
    .dp-aside__close.el-button { display: inline-flex; }
    .dp-menu-btn.el-button { display: inline-flex; }
    .dp-header { padding: 0 24px; }
    .dp-main { padding: 32px 24px; gap: 24px; }
}

@media (max-width: 767.98px) {
    .dp-header { height: 68px; padding: 0 16px; gap: 8px; }
    .dp-header__search { justify-content: flex-start; }
    .dp-search-input .el-input__wrapper { height: 40px; padding-left: 12px; }
    .dp-ask-ai-btn.el-button .dp-ask-ai-btn__label { display: none; }
    .dp-ask-ai-btn.el-button { padding: 0; width: 40px; justify-content: center; gap: 0; }
    .dp-header__actions { gap: 10px; padding-left: 0; }
    .dp-main { padding: 24px 16px; gap: 20px; }
    .dp-aside { width: 84vw; max-width: 320px; }
}

@media (max-width: 479.98px) {
    .dp-header { padding: 0 12px; }
    .dp-header__search { display: none; }
    .dp-main { padding: 20px 12px; gap: 16px; }
}
</style>
