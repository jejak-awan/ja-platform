import type { RouteRecordRaw } from 'vue-router'

// Theme Page Resolver
const ThemePageResolver = () => import('@/shared/components/ThemePageResolver.vue')

// Frontend theme routes
const frontendRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'frontend',
        component: () => import('@/modules/Cms/layouts/FrontendLayout.vue'),
        meta: { public: true },
        children: [
            {
                path: '',
                name: 'home',
                component: ThemePageResolver,
                props: { page: 'Home' },
                meta: {
                    title: 'Home',
                    description: 'JA-Platform - Modern Content Management System',
                }
            },
            {
                path: 'blog',
                name: 'blog',
                component: ThemePageResolver,
                props: { page: 'Blog' },
                meta: {
                    title: 'Blog',
                    description: 'Latest articles and updates',
                }
            },
            {
                path: 'blog/:slug',
                name: 'post',
                component: ThemePageResolver,
                props: { page: 'Post' },
                meta: {
                    title: 'Post',
                }
            },
            {
                path: 'about',
                name: 'about',
                component: ThemePageResolver,
                props: { page: 'About' },
                meta: {
                    title: 'About Us',
                }
            },
            {
                path: 'contact',
                name: 'contact',
                component: ThemePageResolver,
                props: { page: 'Contact' },
                meta: {
                    title: 'Contact',
                }
            },
            {
                path: 'search',
                name: 'search',
                component: ThemePageResolver,
                props: { page: 'Search' },
                meta: {
                    title: 'Search',
                }
            },
            {
                path: 'terms',
                name: 'terms',
                component: () => import('@/modules/System/views/legal/Terms.vue'),
                meta: {
                    title: 'Terms of Service',
                }
            },
            {
                path: 'privacy',
                name: 'privacy',
                component: () => import('@/modules/System/views/legal/Privacy.vue'),
                meta: {
                    title: 'Privacy Policy',
                }
            },
            {
                path: 'akademik',
                name: 'academic',
                component: ThemePageResolver,
                props: { page: 'Academic' },
                meta: { title: 'Akademik' }
            },
            {
                path: 'jurusan',
                name: 'vocational',
                component: ThemePageResolver,
                props: { page: 'Vocational' },
                meta: { title: 'Kompetensi Keahlian' }
            },
            {
                path: 'karir',
                name: 'career-center',
                component: ThemePageResolver,
                props: { page: 'CareerCenter' },
                meta: { title: 'Bursa Kerja & Karir' }
            },
            {
                path: 'prestasi',
                name: 'achievement',
                component: ThemePageResolver,
                props: { page: 'Achievement' },
                meta: { title: 'Prestasi' }
            },
            {
                path: 'ppdb',
                name: 'ppdb',
                component: ThemePageResolver,
                props: { page: 'PPDB' },
                meta: { title: 'Pendaftaran Siswa Baru' }
            },

            {
                path: 'graduation',
                name: 'graduation',
                component: () => import('@/modules/School/views/public/Graduation.vue'),
                meta: { title: 'Pengumuman Kelulusan' }
            },

            // Dynamic content route (must be last in children)
            {
                path: ':slug',
                name: 'page',
                component: ThemePageResolver,
                props: { page: 'Page' },
            },
        ]
    },
]

export default frontendRoutes
