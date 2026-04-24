import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import type { MenuItemDefinition } from '@/types/cms/menu';

const definition: MenuItemDefinition = {
    name: 'page',
    label: 'features.menus.form.types.page',
    category: 'content',
    icon: FileText,
    color: 'blue',
    description: 'Link to a page from your site',
    defaultTitle: 'Page',

    // Data source for fetching pages
    dataSource: {
        endpoint: '/admin/cms/contents?type=page&status=published',
        labelField: 'title',
        valueField: 'id'
    },

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'features.menus.form.label',
            required: true,
            placeholder: 'features.menus.form.labelPlaceholder'
        },
        {
            key: 'target_id',
            type: 'data_select',
            label: 'features.menus.form.selectPage',
            required: true,
            source: '/admin/cms/contents?type=page&status=published',
            labelField: 'title',
            valueField: 'id'
        },
        {
            key: 'open_in_new_tab',
            type: 'boolean',
            label: 'features.menus.form.openInNewTab',
            default: false
        },
        {
            key: 'icon',
            type: 'icon_picker',
            label: 'features.menus.form.icon',
            default: null
        },
        {
            key: 'css_class',
            type: 'text',
            label: 'features.menus.form.cssClasses',
            placeholder: 'features.menus.form.placeholders.cssClasses'
        },
        // Mega menu settings
        {
            key: 'mega_menu_layout',
            type: 'select',
            label: 'features.menus.form.megaMenuLayout',
            options: [
                { label: 'features.menus.form.options.default', value: 'default' },
                { label: 'features.menus.form.options.grid2', value: 'grid-2' },
                { label: 'features.menus.form.options.grid3', value: 'grid-3' },
                { label: 'features.menus.form.options.full', value: 'full' }
            ],
            default: 'default',
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_show_dividers',
            type: 'boolean',
            label: 'features.menus.form.showDividers',
            default: false,
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_column',
            type: 'number',
            label: 'features.menus.form.columnNumber',
            default: 0,
            min: 0,
            max: 6,
            group: 'mega_menu'
        },
        {
            key: 'heading',
            type: 'text',
            label: 'features.menus.form.columnHeading',
            placeholder: 'Optional heading text',
            group: 'mega_menu'
        },
        {
            key: 'hide_label',
            type: 'boolean',
            label: 'features.menus.form.hideLabel',
            default: false,
            group: 'mega_menu'
        },
        // Badge
        {
            key: 'badge',
            type: 'text',
            label: 'features.menus.form.badgeText',
            placeholder: 'New',
            group: 'badge'
        },
        {
            key: 'badge_color',
            type: 'select',
            label: 'features.menus.form.badgeColor',
            options: [
                { label: 'features.menus.form.options.primary', value: 'primary' },
                { label: 'features.menus.form.options.secondary', value: 'secondary' },
                { label: 'features.menus.form.options.success', value: 'success' },
                { label: 'features.menus.form.options.warning', value: 'warning' },
                { label: 'features.menus.form.options.danger', value: 'danger' }
            ],
            default: 'primary',
            group: 'badge'
        },
        // Image
        {
            key: 'image',
            type: 'media',
            label: 'features.menus.form.image',
            group: 'appearance'
        },
        {
            key: 'image_size',
            type: 'select',
            label: 'features.menus.form.imageSize',
            options: [
                { label: 'features.menus.form.options.auto169', value: 'auto' },
                { label: 'features.menus.form.options.landscape_sm', value: 'landscape_sm' },
                { label: 'features.menus.form.options.landscape_md', value: 'landscape_md' },
                { label: 'features.menus.form.options.landscape_lg', value: 'landscape_lg' },
                { label: 'features.menus.form.options.portrait_sm', value: 'portrait_sm' },
                { label: 'features.menus.form.options.portrait_md', value: 'portrait_md' },
                { label: 'features.menus.form.options.full169', value: 'full' }
            ],
            default: 'auto',
            group: 'appearance'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'features.menus.form.promotionDescription',
            placeholder: 'Add a quote or short description over the image',
            group: 'appearance'
        }
    ]
};

export default definition;
