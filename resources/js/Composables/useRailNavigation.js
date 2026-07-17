import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useRailNavigation() {
    const page = usePage();

    const userInitials = computed(() => {
        const name = page.props.auth?.user?.name ?? '';
        return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase()).join('') || 'CO';
    });

    const railLinks = computed(() => [
        { label: 'Overview',   href: route('dashboard'),    active: route().current('dashboard'),    inertia: true,  icon: 'grid'                          },
        { label: 'Lots',       href: '#',                   active: false,                           inertia: false, icon: 'cup'                           },
        { label: 'My Bids',    href: '#',                   active: false,                           inertia: false, icon: 'card'                          },
        { label: 'Origins',    href: '#',                   active: false,                           inertia: false, icon: 'shield',    dividerBefore: true },
        { label: 'Grading',    href: '#',                   active: false,                           inertia: false, icon: 'clipboard'                     },
        { label: 'Reports',    href: '#',                   active: false,                           inertia: false, icon: 'chart'                         },
        { label: 'Alerts · 3', href: '#',                   active: false,                           inertia: false, icon: 'bell',      dot: true          },
        { label: 'Profile',    href: '#',                   active: false,                           inertia: false, icon: 'user',      dividerBefore: true },
    ]);

    return { userInitials, railLinks };
}
