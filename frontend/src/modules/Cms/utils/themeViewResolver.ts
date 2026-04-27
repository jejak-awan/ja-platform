import type { Theme } from '@/composables/useTheme'

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
    const expectedSuffix = `themes/${slug}/${pageName}.vue`.toLowerCase()
    const found = Object.keys(viewModules).find((key) => {
      const k = key.toLowerCase()
      return k.endsWith(expectedSuffix) || k.includes(`/${slug}/${pageName.toLowerCase()}.vue`)
    })
    if (found) return found
  }

  return undefined
}

