import type { route as ZiggyRoute } from '../../vendor/tightenco/ziggy/src/js/index';

declare global {
    const route: typeof ZiggyRoute;
}

export {};
