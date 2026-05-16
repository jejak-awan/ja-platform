import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import type { MenuItemDefinition } from '@/modules/Cms/types/menu';

const definition: MenuItemDefinition = {
    name: 'page',
    label: 'modules.cms.menus.form.types.page',
    category: 'content',
    icon: FileText,
    color: 'blue',
    description: 'Link to a page from your site',
    defaultTitle: 'Page',

    // Data source for fetching pages
    dataSource: {
        endpoint: '/manage/cms/contents?type=page&status=published',
        labelField: 'title',
        valueField: 'id'
    },

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'modules.cms.menus.form.label',
            required: true,
            placeholder: 'modules.cms.menus.form.labelPlaceholder'
        },
        {
            key: 'target_id',
            type: 'data_select',
            label: 'modules.cms.menus.form.selectPage',
            required: true,
            source: '/manage/cms/contents?type=page&status=published',
            labelField: 'title',
            valueField: 'id'
        },
        {
            key: 'open_in_new_tab',
            type: 'boolean',
            label: 'modules.cms.menus.form.openInNewTab',
            default: false
        },
        {
            key: 'icon',
            type: 'icon_picker',
            label: 'modules.cms.menus.form.icon',
            default: null
        },
        {
            key: 'css_class',
            type: 'text',
            label: 'modules.cms.menus.form.cssClasses',
            placeholder: 'modules.cms.menus.form.placeholders.cssClasses'
        },
        // Mega menu settings
        {
            key: 'mega_menu_layout',
            type: 'select',
            label: 'modules.cms.menus.form.megaMenuLayout',
            options: [
                { label: 'modules.cms.menus.form.options.default', value: 'default' },
                { label: 'modules.cms.menus.form.options.grid2', value: 'grid-2' },
                { label: 'modules.cms.menus.form.options.grid3', value: 'grid-3' },
                { label: 'modules.cms.menus.form.options.full', value: 'full' }
            ],
            default: 'default',
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_show_dividers',
            type: 'boolean',
            label: 'modules.cms.menus.form.showDividers',
            default: false,
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_column',
            type: 'number',
            label: 'modules.cms.menus.form.columnNumber',
            default: 0,
            min: 0,
            max: 6,
            group: 'mega_menu'
        },
        {
            key: 'heading',
            type: 'text',
            label: 'modules.cms.menus.form.columnHeading',
            placeholder: 'Optional heading text',
            group: 'mega_menu'
        },
        {
            key: 'hide_label',
            type: 'boolean',
            label: 'modules.cms.menus.form.hideLabel',
            default: false,
            group: 'mega_menu'
        },
        // Badge
        {
            key: 'badge',
            type: 'text',
            label: 'modules.cms.menus.form.badgeText',
            placeholder: 'New',
            group: 'badge'
        },
        {
            key: 'badge_color',
            type: 'select',
            label: 'modules.cms.menus.form.badgeColor',
            options: [
                { label: 'modules.cms.menus.form.options.primary', value: 'primary' },
                { label: 'modules.cms.menus.form.options.secondary', value: 'secondary' },
                { label: 'modules.cms.menus.form.options.success', value: 'success' },
                { label: 'modules.cms.menus.form.options.warning', value: 'warning' },
                { label: 'modules.cms.menus.form.options.danger', value: 'danger' }
            ],
            default: 'primary',
            group: 'badge'
        },
        // Image
        {
            key: 'image',
            type: 'media',
            label: 'modules.cms.menus.form.image',
            group: 'appearance'
        },
        {
            key: 'image_size',
            type: 'select',
            label: 'modules.cms.menus.form.imageSize',
            options: [
                { label: 'modules.cms.menus.form.options.auto169', value: 'auto' },
                { label: 'modules.cms.menus.form.options.landscape_sm', value: 'landscape_sm' },
                { label: 'modules.cms.menus.form.options.landscape_md', value: 'landscape_md' },
                { label: 'modules.cms.menus.form.options.landscape_lg', value: 'landscape_lg' },
                { label: 'modules.cms.menus.form.options.portrait_sm', value: 'portrait_sm' },
                { label: 'modules.cms.menus.form.options.portrait_md', value: 'portrait_md' },
                { label: 'modules.cms.menus.form.options.full169', value: 'full' }
            ],
            default: 'auto',
            group: 'appearance'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'modules.cms.menus.form.promotionDescription',
            placeholder: 'Add a quote or short description over the image',
            group: 'appearance'
        }
    ]
};

export default definition;
