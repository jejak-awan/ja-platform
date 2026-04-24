/**
 * useGsapAnimations — Reusable GSAP animation composable for the Janari theme.
 *
 * Provides scroll-triggered reveals, parallax, text animations, counter-up,
 * staggered children, magnetic hover, and more.
 *
 * All animations respect `prefers-reduced-motion` and auto-cleanup on unmount.
 */

import { onUnmounted, ref } from 'vue'
import { gsap, ScrollTrigger } from '@/lib/gsap'
import { useTheme } from '@/composables/useTheme'

// ── Types ──────────────────────────────────────────────────────────────

export interface AnimationOptions {
  /** Delay before animation starts (seconds) */
  delay?: number
  /** Duration of the animation (seconds) */
  duration?: number
  /** GSAP ease string */
  ease?: string
  /** ScrollTrigger start position, default: 'top 85%' */
  start?: string
  /** ScrollTrigger end position */
  end?: string
  /** Whether to toggle on reverse scroll */
  toggleActions?: string
  /** Distance for directional animations (px) */
  distance?: number
  /** Stagger delay between children */
  stagger?: number
  /** Scrub value (true or number) for scroll-linked animation */
  scrub?: boolean | number
}

// ── Composable ─────────────────────────────────────────────────────────

export function useGsapAnimations() {
  const { getSetting } = useTheme()
  const triggers: ScrollTrigger[] = []
  const tweens: gsap.core.Tween[] = []
  const timelines: gsap.core.Timeline[] = []
  const disposers: Array<() => void> = []
  const prefersReducedMotion = ref(false)
  let motionMql: MediaQueryList | null = null
  let motionChangeHandler: ((e: MediaQueryListEvent) => void) | null = null

  // Check reduced motion preference
  if (typeof window !== 'undefined') {
    motionMql = window.matchMedia('(prefers-reduced-motion: reduce)')
    prefersReducedMotion.value = motionMql.matches
    motionChangeHandler = (e: MediaQueryListEvent) => {
      prefersReducedMotion.value = e.matches
    }
    motionMql.addEventListener('change', motionChangeHandler)
  }

  const isAnimationEnabled = (): boolean => getSetting('animation_enabled', true) !== false
  const isParallaxEnabled = (): boolean => getSetting('parallax_enabled', true) !== false
  const intensityScale = (): number => {
    const level = String(getSetting('animation_intensity', 'normal') || 'normal')
    if (level === 'subtle') return 0.8
    if (level === 'dramatic') return 1.2
    return 1
  }

  const normalizeOptions = (opts: AnimationOptions = {}, allowInReducedMotion = true): Required<Pick<AnimationOptions, 'delay' | 'duration' | 'ease' | 'start' | 'distance' | 'stagger'>> => {
    const scale = intensityScale()
    const reduced = prefersReducedMotion.value && allowInReducedMotion
    return {
      delay: opts.delay ?? 0,
      duration: reduced ? Math.min(opts.duration ?? 0.8, 0.24) : (opts.duration ?? 0.8) * scale,
      ease: opts.ease ?? 'power3.out',
      start: opts.start ?? 'top 85%',
      distance: reduced ? 0 : (opts.distance ?? 60) * scale,
      stagger: reduced ? 0 : (opts.stagger ?? 0.12) * scale,
    }
  }

  /**
   * Skip animation entirely when globally disabled or reduced motion requires no animation.
   */
  const shouldSkip = (el: Element | Element[], allowInReducedMotion = true): boolean => {
    if (!isAnimationEnabled() || (prefersReducedMotion.value && !allowInReducedMotion)) {
      // Make elements visible immediately
      const elements = Array.isArray(el) ? el : [el]
      elements.forEach((e) => {
        gsap.set(e, { opacity: 1, y: 0, x: 0, scale: 1 })
      })
      return true
    }
    return false
  }

  // ── Track & Cleanup ───────────────────────────────────────────────

  const track = (item: ScrollTrigger | gsap.core.Tween | gsap.core.Timeline) => {
    if (item instanceof ScrollTrigger) {
      triggers.push(item)
    } else if ('totalDuration' in item && 'add' in item) {
      timelines.push(item as gsap.core.Timeline)
    } else {
      tweens.push(item as gsap.core.Tween)
    }
  }

  const cleanup = () => {
    triggers.forEach((t) => t.kill())
    tweens.forEach((t) => t.kill())
    timelines.forEach((t) => t.kill())
    disposers.forEach((fn) => fn())
    triggers.length = 0
    tweens.length = 0
    timelines.length = 0
    disposers.length = 0
    if (motionMql && motionChangeHandler) {
      motionMql.removeEventListener('change', motionChangeHandler)
      motionChangeHandler = null
    }
  }

  onUnmounted(() => {
    cleanup()
  })

  // ── Animation Primitives ──────────────────────────────────────────

  /**
   * Fade in + slide up on scroll
   */
  const fadeInUp = (el: Element | string, opts: AnimationOptions = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target) return
    if (shouldSkip(target, true)) return

    const { delay, duration, ease, start, distance } = normalizeOptions(opts, true)
    const toggleActions = opts.toggleActions ?? 'play none none none'

    gsap.set(target, { opacity: 0, y: distance })

    const tween = gsap.to(target, {
      opacity: 1,
      y: 0,
      duration,
      delay,
      ease,
      scrollTrigger: {
        trigger: target,
        start,
        toggleActions,
      },
    })

    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Fade in from left
   */
  const fadeInLeft = (el: Element | string, opts: AnimationOptions = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target) return
    if (shouldSkip(target, true)) return
    const { delay, duration, ease, start, distance } = normalizeOptions({ ...opts, distance: opts.distance ?? 80 }, true)

    gsap.set(target, { opacity: 0, x: -distance })
    const tween = gsap.to(target, {
      opacity: 1,
      x: 0,
      duration,
      delay,
      ease,
      scrollTrigger: { trigger: target, start, toggleActions: 'play none none none' },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Fade in from right
   */
  const fadeInRight = (el: Element | string, opts: AnimationOptions = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target) return
    if (shouldSkip(target, true)) return
    const { delay, duration, ease, start, distance } = normalizeOptions({ ...opts, distance: opts.distance ?? 80 }, true)

    gsap.set(target, { opacity: 0, x: distance })
    const tween = gsap.to(target, {
      opacity: 1,
      x: 0,
      duration,
      delay,
      ease,
      scrollTrigger: { trigger: target, start, toggleActions: 'play none none none' },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Scale reveal (zoom in from smaller)
   */
  const scaleReveal = (el: Element | string, opts: AnimationOptions = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target) return
    if (shouldSkip(target, true)) return
    const { delay, duration, ease, start } = normalizeOptions({ ...opts, distance: 0 }, true)

    gsap.set(target, { opacity: 0, scale: 0.85 })
    const tween = gsap.to(target, {
      opacity: 1,
      scale: 1,
      duration,
      delay,
      ease,
      scrollTrigger: { trigger: target, start, toggleActions: 'play none none none' },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Parallax background effect — element moves at different speed on scroll
   */
  const parallaxBg = (el: Element | string, opts: { speed?: number; start?: string; end?: string } = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target || !target.isConnected) return
    if (shouldSkip(target, false) || !isParallaxEnabled()) return

    const { speed = 0.3, start = 'top bottom', end = 'bottom top' } = opts
    const adjustedSpeed = speed * intensityScale()

    const tween = gsap.to(target, {
      yPercent: adjustedSpeed * 100,
      ease: 'none',
      scrollTrigger: {
        trigger: target,
        start,
        end,
        scrub: 1,
      },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Stagger children elements (for grids, lists, etc.)
   */
  const staggerChildren = (
    parent: Element | string,
    childSelector: string,
    opts: AnimationOptions = {},
  ) => {
    const container = typeof parent === 'string' ? document.querySelector(parent) : parent
    if (!container || !container.isConnected) return

    const children = container.querySelectorAll(childSelector)
    if (!children.length) return
    if (shouldSkip(Array.from(children), true)) return
    const { delay, duration, ease, start, distance, stagger } = normalizeOptions({ ...opts, duration: opts.duration ?? 0.6, distance: opts.distance ?? 50, stagger: opts.stagger ?? 0.12 }, true)

    gsap.set(children, { opacity: 0, y: distance })

    const tween = gsap.to(children, {
      opacity: 1,
      y: 0,
      duration,
      delay,
      ease,
      stagger,
      scrollTrigger: {
        trigger: container,
        start,
        toggleActions: 'play none none none',
      },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * LEGACY: Text reveal animation — words or characters fade in sequentially.
   * @deprecated Use JanariSplitText.vue component and splitTextRevealSafe instead.
   * This method is destructive to the DOM and can break Vue's VDOM reconciliation.
   */
  const splitTextReveal = (el: Element | string, opts: AnimationOptions & { type?: 'words' | 'chars' } = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target || !(target instanceof HTMLElement) || !target.isConnected) return
    
    // Prevent double-splitting which corrupts DOM and breaks VDOM
    if (target.hasAttribute('data-gsap-split')) return
    if (shouldSkip(target, true)) return

    const normalized = normalizeOptions({ ...opts, duration: opts.duration ?? 0.6, stagger: opts.stagger ?? 0.04 }, true)
    const { delay, duration, ease, start } = normalized
    const stagger = normalized.stagger
    const type = opts.type ?? 'words'

    const text = target.textContent || ''
    const units = type === 'chars' ? text.split('') : text.split(/\s+/)

    // Preserve original HTML and mark as split
    target.setAttribute('aria-label', text)
    target.setAttribute('data-gsap-split', 'true')
    target.innerHTML = units
      .map((unit) => `<span class="gsap-split-unit" style="display:inline-block;overflow:hidden"><span class="gsap-split-inner" style="display:inline-block">${unit}</span></span>`)
      .join(type === 'chars' ? '' : '&nbsp;')

    const inners = target.querySelectorAll('.gsap-split-inner')
    gsap.set(inners, { yPercent: 110, opacity: 0 })

    const tween = gsap.to(inners, {
      yPercent: 0,
      opacity: 1,
      duration,
      delay,
      ease,
      stagger,
      scrollTrigger: {
        trigger: target,
        start,
        toggleActions: 'play none none none',
      },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Animated counter — number counts up from 0 to target value.
   * NOTE: This is destructive to existing child text nodes. 
   * Use with v-once or ensure the element is not reactively managed by Vue.
   */
  const counterUp = (el: Element | string, endValue: number, opts: AnimationOptions = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target || !(target instanceof HTMLElement)) return

    if (!isAnimationEnabled() || prefersReducedMotion.value) {
      target.textContent = String(endValue)
      return
    }

    const normalized = normalizeOptions({ ...opts, duration: opts.duration ?? 2, distance: 0, stagger: 0 }, true)
    const { duration, ease, start } = normalized
    const proxy = { val: 0 }

    // Check if has suffix (like +, %)
    const originalText = target.textContent || ''
    const suffix = originalText.replace(/[\d.,]/g, '').trim()

    const tween = gsap.to(proxy, {
      val: endValue,
      duration,
      ease,
      onUpdate: () => {
        // Only update if target is still in DOM
        if (target.isConnected) {
            target.textContent = Math.round(proxy.val).toLocaleString('id-ID') + suffix
        }
      },
      scrollTrigger: {
        trigger: target,
        start,
        toggleActions: 'play none none none',
      },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Magnetic hover effect — element follows cursor subtly
   */
  const magneticHover = (el: Element | string, strength: number = 0.3) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target || !(target instanceof HTMLElement)) return
    if (!isAnimationEnabled() || prefersReducedMotion.value) return

    const onMove = (e: MouseEvent) => {
      const rect = target.getBoundingClientRect()
      const x = e.clientX - rect.left - rect.width / 2
      const y = e.clientY - rect.top - rect.height / 2
      gsap.to(target, {
        x: x * strength * intensityScale(),
        y: y * strength * intensityScale(),
        duration: 0.4,
        ease: 'power3.out',
      })
    }

    const onLeave = () => {
      gsap.to(target, { x: 0, y: 0, duration: 0.6, ease: 'elastic.out(1, 0.5)' })
    }

    target.addEventListener('mousemove', onMove)
    target.addEventListener('mouseleave', onLeave)

    // Track disposer so one composable cleanup handles all listener removals.
    const removeListeners = () => {
      target.removeEventListener('mousemove', onMove)
      target.removeEventListener('mouseleave', onLeave)
    }
    disposers.push(removeListeners)
  }

  /**
   * Safe Text reveal animation — targets pre-rendered spans (JanariSplitText.vue).
   * Does NOT modify DOM structure.
   */
  const splitTextRevealSafe = (el: HTMLElement | null, opts: AnimationOptions = {}) => {
    if (!el || !el.isConnected) return
    if (shouldSkip(el, true)) return
    
    // Target the inner spans created by JanariSplitText component
    const inners = el.querySelectorAll('.gsap-split-inner')
    if (!inners.length) return

    const normalized = normalizeOptions({ ...opts, duration: opts.duration ?? 0.6, stagger: opts.stagger ?? 0.04 }, true)
    const { delay, duration, ease, stagger } = normalized
    const start = opts.start ?? 'top 90%'

    // Ensure they are reset before animation
    gsap.set(inners, { yPercent: 110, opacity: 0 })

    const tween = gsap.to(inners, {
      yPercent: 0,
      opacity: 1,
      duration,
      delay,
      ease,
      stagger,
      scrollTrigger: {
        trigger: el,
        start,
        toggleActions: 'play none none none',
      },
    })
    if (tween.scrollTrigger) track(tween.scrollTrigger)
    track(tween)
  }

  /**
   * Floating animation — continuous gentle Y oscillation (for decorative elements)
   */
  const floatingAnimation = (el: Element | string, opts: { distance?: number; duration?: number } = {}) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target) return
    if (!isAnimationEnabled() || prefersReducedMotion.value) return

    const scale = intensityScale()
    const { distance = 15, duration = 3 } = opts

    const tween = gsap.to(target, {
      y: distance * scale,
      duration: duration * scale,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
    })
    track(tween)
  }

  /**
   * Marquee — infinite horizontal scroll.
   * IMPORTANT: This method NO LONGER duplicates HTML. 
   * Ensure the parent container has duplicated items for a seamless loop.
   */
  const marquee = (container: Element | string, opts: { speed?: number; direction?: 'left' | 'right' } = {}) => {
    const el = typeof container === 'string' ? document.querySelector(container) : container
    if (!el || !(el instanceof HTMLElement)) return
    if (!isAnimationEnabled() || prefersReducedMotion.value) return

    const { speed = 30, direction = 'left' } = opts
    const adjustedSpeed = speed * intensityScale()

    // Calculate width of original content (assumes items are duplicated)
    const totalWidth = el.scrollWidth / 2
    const xFrom = direction === 'left' ? 0 : -totalWidth
    const xTo = direction === 'left' ? -totalWidth : 0

    gsap.set(el, { x: xFrom })
    const tween = gsap.to(el, {
      x: xTo,
      duration: totalWidth / adjustedSpeed,
      ease: 'none',
      repeat: -1,
    })
    track(tween)
  }

  /**
   * Smart header — hide on scroll down, show on scroll up
   */
  const smartHeader = (el: Element | string) => {
    const target = typeof el === 'string' ? document.querySelector(el) : el
    if (!target || !(target instanceof HTMLElement)) return

    if (!isAnimationEnabled()) return
    let lastScrollY = 0

    const st = ScrollTrigger.create({
      start: 'top top',
      end: 'max',
      onUpdate: (self) => {
        const currentScrollY = self.scroll()
        const direction = currentScrollY > lastScrollY ? 'down' : 'up'
        const distance = Math.abs(currentScrollY - lastScrollY)

        // Only trigger after scrolling past header height and with minimum distance
        if (currentScrollY > 80 && distance > 10) {
          if (direction === 'down') {
            gsap.to(target, { yPercent: -100, duration: 0.3, ease: 'power2.inOut' })
          } else {
            gsap.to(target, { yPercent: 0, duration: 0.3, ease: 'power2.inOut' })
          }
        }

        lastScrollY = currentScrollY
      },
    })
    track(st)
  }

  /**
   * Create a GSAP timeline (auto-tracked for cleanup)
   */
  const createTimeline = (vars?: gsap.TimelineVars): gsap.core.Timeline => {
    const tl = gsap.timeline(vars)
    track(tl)
    return tl
  }

  /**
   * Batch utility: apply fadeInUp to all elements matching a selector
   */
  const batchFadeInUp = (selector: string, opts: AnimationOptions = {}) => {
    const elements = document.querySelectorAll(selector)
    elements.forEach((el, i) => {
      fadeInUp(el, { ...opts, delay: (opts.delay || 0) + i * (opts.stagger || 0.1) })
    })
  }

  return {
    // State
    prefersReducedMotion,

    // Core animations
    fadeInUp,
    fadeInLeft,
    fadeInRight,
    scaleReveal,
    parallaxBg,
    staggerChildren,
    splitTextReveal,
    splitTextRevealSafe,
    counterUp,
    magneticHover,
    floatingAnimation,
    marquee,
    smartHeader,

    // Utilities
    createTimeline,
    batchFadeInUp,
    cleanup,

    // Direct GSAP access (for custom animations)
    gsap,
    ScrollTrigger,
  }
}
