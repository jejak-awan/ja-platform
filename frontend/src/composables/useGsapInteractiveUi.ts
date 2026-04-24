import { onUnmounted, type Ref } from 'vue'
import { gsap } from '@/lib/gsap'
import { useTheme } from '@/composables/useTheme'

type MaybeContainer = HTMLElement | null | undefined

const INTERACTIVE_SELECTOR = [
  'a',
  'button',
  '[role="button"]',
  'input',
  'textarea',
  'select',
  '.menu-item',
  '.gsap-interactive',
  '[data-gsap-interactive]',
].join(',')

export function useGsapInteractiveUi(containerRef: Ref<MaybeContainer>) {
  const { getSetting } = useTheme()
  let cleanupFn: (() => void) | null = null

  const isEnabled = (): boolean => getSetting('animation_enabled', true) !== false
  const prefersReducedMotion = (): boolean =>
    typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches

  const getTarget = (eventTarget: EventTarget | null): HTMLElement | null => {
    if (!(eventTarget instanceof Element)) return null
    const target = eventTarget.closest(INTERACTIVE_SELECTOR)
    if (!(target instanceof HTMLElement)) return null
    if (target.dataset.gsapInteractive === 'off') return null
    return target
  }

  const setup = () => {
    const root = containerRef.value
    if (!root || cleanupFn) return

    const onMouseOver = (event: Event) => {
      if (!isEnabled() || prefersReducedMotion()) return
      const target = getTarget(event.target)
      if (!target) return
      const related = event instanceof MouseEvent ? (event.relatedTarget as Node | null) : null
      if (related && target.contains(related)) return
      gsap.to(target, {
        y: -1.5,
        scale: 1.01,
        duration: 0.2,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    }

    const onMouseOut = (event: Event) => {
      const target = getTarget(event.target)
      if (!target) return
      const related = event instanceof MouseEvent ? (event.relatedTarget as Node | null) : null
      if (related && target.contains(related)) return
      gsap.to(target, {
        y: 0,
        scale: 1,
        duration: 0.22,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    }

    const onMouseDown = (event: Event) => {
      if (!isEnabled()) return
      const target = getTarget(event.target)
      if (!target) return
      gsap.to(target, {
        scale: 0.985,
        duration: 0.1,
        ease: 'power1.out',
        overwrite: 'auto',
      })
    }

    const onMouseUp = (event: Event) => {
      const target = getTarget(event.target)
      if (!target) return
      gsap.to(target, {
        scale: 1,
        duration: 0.16,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    }

    const onFocusIn = (event: Event) => {
      if (!isEnabled()) return
      const target = getTarget(event.target)
      if (!target) return
      // Use a concrete RGBA color so GSAP's color parser stays stable across browsers.
      gsap.to(target, {
        boxShadow: '0 0 0 2px rgba(34, 211, 238, 0.22)',
        duration: 0.16,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    }

    const onFocusOut = (event: Event) => {
      const target = getTarget(event.target)
      if (!target) return
      gsap.to(target, {
        boxShadow: 'none',
        duration: 0.2,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    }

    root.addEventListener('mouseover', onMouseOver)
    root.addEventListener('mouseout', onMouseOut)
    root.addEventListener('mousedown', onMouseDown)
    root.addEventListener('mouseup', onMouseUp)
    root.addEventListener('focusin', onFocusIn)
    root.addEventListener('focusout', onFocusOut)

    cleanupFn = () => {
      root.removeEventListener('mouseover', onMouseOver)
      root.removeEventListener('mouseout', onMouseOut)
      root.removeEventListener('mousedown', onMouseDown)
      root.removeEventListener('mouseup', onMouseUp)
      root.removeEventListener('focusin', onFocusIn)
      root.removeEventListener('focusout', onFocusOut)
      cleanupFn = null
    }
  }

  const cleanup = () => {
    if (cleanupFn) cleanupFn()
  }

  onUnmounted(cleanup)

  return { setup, cleanup }
}

