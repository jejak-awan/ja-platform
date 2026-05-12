import File from 'lucide-vue-next/dist/esm/icons/file.js';
import type { MenuItemDefinition } from '@/modules/Cms/types/menu';

const definition: MenuItemDefinition = {
    name: 'post',
    label: 'modules.cms.menus.form.types.post',
    category: 'content',
    icon: File,
    color: 'orange',
    description: 'Link to a blog post',
    defaultTitle: 'Post',

    dataSource: {
        endpoint: '/admin/cms/contents?type=post&status=published',
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
            label: 'modules.cms.menus.form.selectPost',
            required: true,
            source: '/admin/cms/contents?type=post&status=published',
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
        }
    ]
};

export default definition;
