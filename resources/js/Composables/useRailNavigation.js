import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useRailNavigation() {
    const page = usePage();

    const userInitials = computed(() => {
        const name = page.props.auth?.user?.name ?? '';
        return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase()).join('') || 'CO';
    });

    // Each entry's `slug` is a Ziggy route name. The href/active state is
    // resolved from that slug at render time, so a link only navigates once
    // the route it names actually exists — no separately hand-maintained
    // href strings to drift out of sync.
    const railLinks = computed(() => [
        { label: 'Overview',   icon: 'grid',      slug: 'dashboard'            },
        { label: 'Lots',       icon: 'cup',       slug: 'lot.index'            },
        { label: 'My Bids',    icon: 'card',      slug: 'bid.index'            },
        { label: 'Origins',    icon: 'shield',    slug: 'origin.index',    dividerBefore: true },
        { label: 'Grading',    icon: 'clipboard', slug: 'grading.index'        },
        { label: 'Reports',    icon: 'chart',     slug: 'reports.index'        },
        { label: 'Alerts · 3', icon: 'bell',      slug: 'notifications.index', dot: true },
        { label: 'Profile',    icon: 'user',      slug: 'profile.show',    dividerBefore: true },
    ].map((link) => ({
        ...link,
        inertia: route().has(link.slug),
        href: route().has(link.slug) ? route(link.slug) : '#',
        active: route().has(link.slug) && route().current(link.slug),
    })));

    return { userInitials, railLinks };
}
