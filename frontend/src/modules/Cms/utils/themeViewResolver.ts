import type { Theme } from '@/modules/Cms/composables/useTheme'

export type ThemeViewModules = Record<string, () => Promise<unknown>>

export const buildThemeViewResolveCandidates = (theme: Theme | null | undefined): string[] => {
  if (!theme) return []

  const slug =
    typeof theme.slug === 'string' && theme.slug.trim() !== ''
      ? theme.slug.trim()
      : ''
  const parentSlug =
    typeof theme.parent_theme === 'string' && theme.parent_theme.trim() !== ''
      ? theme.parent_theme.trim()
      : ''

  if (!slug && !parentSlug) return []
  if (parentSlug && parentSlug !== slug) return [slug, parentSlug].filter(Boolean)
  return [slug].filter(Boolean)
}

export const findThemeViewKey = (
  viewModules: ThemeViewModules,
  themeSlugs: string[],
  pageName: string,
): string | undefined => {
  if (themeSlugs.length === 0) return undefined

  for (const slug of themeSlugs) {
    const slugLower = slug.toLowerCase()
    const pageLower = pageName.toLowerCase()
    
    // Look for: .../themes/{slug}/{pageName}.vue
    const found = Object.keys(viewModules).find((key) => {
      const k = key.toLowerCase().replace(/\\/g, '/') // Normalize separators
      
    // Strict match: ends with themes/slug/page.vue
      if (k.endsWith(`themes/${slugLower}/${pageLower}.vue`)) return true
      
      // Page name match (for components/Header -> header.vue)
      const fileNameLower = pageLower.split('/').pop() + '.vue'
      if (k.includes(`/${slugLower}/`) && k.endsWith(`/${fileNameLower}`)) return true

      // Fallback: contains /slug/ and ends with /page.vue
      return k.includes(`/${slugLower}/`) && k.endsWith(`/${pageLower}.vue`)
    })
    
    if (found) return found
  }

  // GLOBAL FALLBACK (if not found in theme)
  const pageLower = pageName.toLowerCase()
  const fileNameLower = pageLower.split('/').pop() + '.vue'
  
  return Object.keys(viewModules).find((key) => {
      const k = key.toLowerCase().replace(/\\/g, '/')
      return k.endsWith(`/${pageLower}.vue`) || k.endsWith(`/${fileNameLower}`)
  })

  return undefined
}

