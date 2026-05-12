/**
 * Navigation types for admin sidebar
 */

export interface NavItem {
    name?: string;
    to?: string;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    role?: string | string[];
    context?: 'system' | 'foundation' | 'authority' | 'unit' | 'both';
    type?: 'item' | 'divider';
    children?: NavItem[];
}
