export interface ThemeBindingRegistryProp {
    key: string
    labelKey: string
}

export interface ThemeBindingRegistrySlot {
    id: string
    labelKey: string
    props: ThemeBindingRegistryProp[]
}

export interface ThemeBindingRegistryComponent {
    id: string
    nameKey: string
    descriptionKey: string
    icon: 'hero' | 'principal' | 'news' | 'majors' | 'stats' | 'testimonials' | 'cta'
    manifestCategory?: string
    slots: ThemeBindingRegistrySlot[]
}

export const THEME_BINDING_REGISTRY: ThemeBindingRegistryComponent[] = [
    {
        id: 'hero',
        nameKey: 'features.theme_builder.items.hero_section',
        descriptionKey: 'features.theme_builder.items.hero_section_desc',
        icon: 'hero',
        manifestCategory: 'Hero Section',
        slots: [
            {
                id: 'news',
                labelKey: 'features.theme_builder.items.news_overlay',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.news_title' },
                    { key: 'category', labelKey: 'features.theme_builder.items.badge' },
                    { key: 'date', labelKey: 'features.theme_builder.items.date' },
                    { key: 'image', labelKey: 'features.theme_builder.items.thumbnail' },
                    { key: 'url', labelKey: 'features.theme_builder.items.target_url' },
                ],
            },
        ],
    },
    {
        id: 'principal',
        nameKey: 'features.theme_builder.items.principal_welcome_comp',
        descriptionKey: 'features.theme_builder.items.principal_welcome_desc',
        icon: 'principal',
        manifestCategory: 'Education Info',
        slots: [
            {
                id: 'profile',
                labelKey: 'features.theme_builder.items.profile_data',
                props: [
                    { key: 'name', labelKey: 'features.theme_builder.items.name' },
                    { key: 'message', labelKey: 'features.theme_builder.items.message' },
                    { key: 'image', labelKey: 'features.theme_builder.items.photo' },
                    { key: 'title', labelKey: 'features.theme_builder.items.job_title' },
                ],
            },
        ],
    },
    {
        id: 'info',
        nameKey: 'features.theme_builder.items.update_dashboard',
        descriptionKey: 'features.theme_builder.items.update_dashboard_desc',
        icon: 'news',
        slots: [
            {
                id: 'announcements',
                labelKey: 'features.theme_builder.items.col1_announcements',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.title' },
                    { key: 'date', labelKey: 'features.theme_builder.items.date' },
                    { key: 'category', labelKey: 'features.theme_builder.items.cat' },
                    { key: 'url', labelKey: 'features.theme_builder.items.link' },
                ],
            },
            {
                id: 'agenda',
                labelKey: 'features.theme_builder.items.col2_agenda',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.title' },
                    { key: 'date', labelKey: 'features.theme_builder.items.date' },
                    { key: 'category', labelKey: 'features.theme_builder.items.cat' },
                    { key: 'url', labelKey: 'features.theme_builder.items.link' },
                ],
            },
            {
                id: 'holidays',
                labelKey: 'features.theme_builder.items.col3_holidays',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.title' },
                    { key: 'date', labelKey: 'features.theme_builder.items.date' },
                    { key: 'category', labelKey: 'features.theme_builder.items.cat' },
                    { key: 'url', labelKey: 'features.theme_builder.items.link' },
                ],
            },
        ],
    },
    {
        id: 'majors',
        nameKey: 'features.theme_builder.items.majors',
        descriptionKey: 'features.theme_builder.items.majors_desc',
        icon: 'majors',
        slots: [
            {
                id: 'programs',
                labelKey: 'features.theme_builder.items.majors_grid',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.major' },
                    { key: 'description', labelKey: 'features.theme_builder.items.short_info' },
                    { key: 'image', labelKey: 'features.theme_builder.items.icon_photo' },
                ],
            },
        ],
    },
    {
        id: 'stats',
        nameKey: 'features.theme_builder.items.stats',
        descriptionKey: 'features.theme_builder.items.stats_desc',
        icon: 'stats',
        slots: [
            {
                id: 'counters',
                labelKey: 'features.theme_builder.items.stat_counters',
                props: [
                    { key: 'label', labelKey: 'features.theme_builder.items.metric' },
                    { key: 'value', labelKey: 'features.theme_builder.items.counter' },
                    { key: 'suffix', labelKey: 'features.theme_builder.items.suffix' },
                ],
            },
        ],
    },
    {
        id: 'testimonials',
        nameKey: 'features.theme_builder.items.testimonials',
        descriptionKey: 'features.theme_builder.items.testimonials_desc',
        icon: 'testimonials',
        slots: [
            {
                id: 'items',
                labelKey: 'features.theme_builder.items.testimonial_list',
                props: [
                    { key: 'name', labelKey: 'features.theme_builder.items.name' },
                    { key: 'role', labelKey: 'features.theme_builder.items.role' },
                    { key: 'content', labelKey: 'features.theme_builder.items.quote' },
                    { key: 'image', labelKey: 'features.theme_builder.items.avatar' },
                ],
            },
        ],
    },
    {
        id: 'partners',
        nameKey: 'features.theme_builder.items.partners_section',
        descriptionKey: 'features.theme_builder.items.partners_desc',
        icon: 'majors',
        manifestCategory: 'Partners Section',
        slots: [
            {
                id: 'partners',
                labelKey: 'features.theme_builder.items.partners_list',
                props: [
                    { key: 'name', labelKey: 'features.theme_builder.items.name' },
                    { key: 'image', labelKey: 'features.theme_builder.items.logo' },
                ],
            },
        ],
    },
    {
        id: 'cta',
        nameKey: 'features.theme_builder.items.cta',
        descriptionKey: 'features.theme_builder.items.cta_desc',
        icon: 'cta',
        manifestCategory: 'CTA Section',
        slots: [
            {
                id: 'content',
                labelKey: 'features.theme_builder.items.active_content',
                props: [
                    { key: 'title', labelKey: 'features.theme_builder.items.header' },
                    { key: 'subtitle', labelKey: 'features.theme_builder.items.detail' },
                    { key: 'buttonText', labelKey: 'features.theme_builder.items.label' },
                    { key: 'buttonUrl', labelKey: 'features.theme_builder.items.path' },
                ],
            },
        ],
    },
]

