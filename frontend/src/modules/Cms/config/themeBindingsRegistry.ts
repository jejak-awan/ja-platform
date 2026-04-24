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
        nameKey: 'features.theme_customizer.items.hero_section',
        descriptionKey: 'features.theme_customizer.items.hero_section_desc',
        icon: 'hero',
        manifestCategory: 'Hero Section',
        slots: [
            {
                id: 'news',
                labelKey: 'features.theme_customizer.items.news_overlay',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.news_title' },
                    { key: 'category', labelKey: 'features.theme_customizer.items.badge' },
                    { key: 'date', labelKey: 'features.theme_customizer.items.date' },
                    { key: 'image', labelKey: 'features.theme_customizer.items.thumbnail' },
                    { key: 'url', labelKey: 'features.theme_customizer.items.target_url' },
                ],
            },
        ],
    },
    {
        id: 'principal',
        nameKey: 'features.theme_customizer.items.principal_welcome_comp',
        descriptionKey: 'features.theme_customizer.items.principal_welcome_desc',
        icon: 'principal',
        manifestCategory: 'Education Info',
        slots: [
            {
                id: 'profile',
                labelKey: 'features.theme_customizer.items.profile_data',
                props: [
                    { key: 'name', labelKey: 'features.theme_customizer.items.name' },
                    { key: 'message', labelKey: 'features.theme_customizer.items.message' },
                    { key: 'image', labelKey: 'features.theme_customizer.items.photo' },
                    { key: 'title', labelKey: 'features.theme_customizer.items.job_title' },
                ],
            },
        ],
    },
    {
        id: 'info',
        nameKey: 'features.theme_customizer.items.update_dashboard',
        descriptionKey: 'features.theme_customizer.items.update_dashboard_desc',
        icon: 'news',
        slots: [
            {
                id: 'announcements',
                labelKey: 'features.theme_customizer.items.col1_announcements',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.title' },
                    { key: 'date', labelKey: 'features.theme_customizer.items.date' },
                    { key: 'category', labelKey: 'features.theme_customizer.items.cat' },
                    { key: 'url', labelKey: 'features.theme_customizer.items.link' },
                ],
            },
            {
                id: 'agenda',
                labelKey: 'features.theme_customizer.items.col2_agenda',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.title' },
                    { key: 'date', labelKey: 'features.theme_customizer.items.date' },
                    { key: 'category', labelKey: 'features.theme_customizer.items.cat' },
                    { key: 'url', labelKey: 'features.theme_customizer.items.link' },
                ],
            },
            {
                id: 'holidays',
                labelKey: 'features.theme_customizer.items.col3_holidays',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.title' },
                    { key: 'date', labelKey: 'features.theme_customizer.items.date' },
                    { key: 'category', labelKey: 'features.theme_customizer.items.cat' },
                    { key: 'url', labelKey: 'features.theme_customizer.items.link' },
                ],
            },
        ],
    },
    {
        id: 'majors',
        nameKey: 'features.theme_customizer.items.majors',
        descriptionKey: 'features.theme_customizer.items.majors_desc',
        icon: 'majors',
        slots: [
            {
                id: 'programs',
                labelKey: 'features.theme_customizer.items.majors_grid',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.major' },
                    { key: 'description', labelKey: 'features.theme_customizer.items.short_info' },
                    { key: 'image', labelKey: 'features.theme_customizer.items.icon_photo' },
                ],
            },
        ],
    },
    {
        id: 'stats',
        nameKey: 'features.theme_customizer.items.stats',
        descriptionKey: 'features.theme_customizer.items.stats_desc',
        icon: 'stats',
        slots: [
            {
                id: 'counters',
                labelKey: 'features.theme_customizer.items.stat_counters',
                props: [
                    { key: 'label', labelKey: 'features.theme_customizer.items.metric' },
                    { key: 'value', labelKey: 'features.theme_customizer.items.counter' },
                    { key: 'suffix', labelKey: 'features.theme_customizer.items.suffix' },
                ],
            },
        ],
    },
    {
        id: 'testimonials',
        nameKey: 'features.theme_customizer.items.testimonials',
        descriptionKey: 'features.theme_customizer.items.testimonials_desc',
        icon: 'testimonials',
        slots: [
            {
                id: 'items',
                labelKey: 'features.theme_customizer.items.testimonial_list',
                props: [
                    { key: 'name', labelKey: 'features.theme_customizer.items.name' },
                    { key: 'role', labelKey: 'features.theme_customizer.items.role' },
                    { key: 'content', labelKey: 'features.theme_customizer.items.quote' },
                    { key: 'image', labelKey: 'features.theme_customizer.items.avatar' },
                ],
            },
        ],
    },
    {
        id: 'partners',
        nameKey: 'features.theme_customizer.items.partners_section',
        descriptionKey: 'features.theme_customizer.items.partners_desc',
        icon: 'majors',
        manifestCategory: 'Partners Section',
        slots: [
            {
                id: 'partners',
                labelKey: 'features.theme_customizer.items.partners_list',
                props: [
                    { key: 'name', labelKey: 'features.theme_customizer.items.name' },
                    { key: 'image', labelKey: 'features.theme_customizer.items.logo' },
                ],
            },
        ],
    },
    {
        id: 'cta',
        nameKey: 'features.theme_customizer.items.cta',
        descriptionKey: 'features.theme_customizer.items.cta_desc',
        icon: 'cta',
        manifestCategory: 'CTA Section',
        slots: [
            {
                id: 'content',
                labelKey: 'features.theme_customizer.items.active_content',
                props: [
                    { key: 'title', labelKey: 'features.theme_customizer.items.header' },
                    { key: 'subtitle', labelKey: 'features.theme_customizer.items.detail' },
                    { key: 'buttonText', labelKey: 'features.theme_customizer.items.label' },
                    { key: 'buttonUrl', labelKey: 'features.theme_customizer.items.path' },
                ],
            },
        ],
    },
]

