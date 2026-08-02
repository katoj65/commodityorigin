import * as ElementPlusIconsVue from '@element-plus/icons-vue';

export const DEFAULT_ICON = 'Setting';

/**
 * Resolve a DB-stored icon name to a globally-registered Element Plus icon
 * component name, falling back to a default when the stored value is
 * missing or isn't a real icon in the set (e.g. a typo).
 */
export function resolveIcon(name, fallback = DEFAULT_ICON) {
    return name && Object.prototype.hasOwnProperty.call(ElementPlusIconsVue, name) ? name : fallback;
}
