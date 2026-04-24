/**
 * GSAP Central Setup
 * 
 * Import GSAP and register all plugins from this single entry point.
 * This prevents tree-shaking issues and ensures plugins are registered once.
 * 
 * Usage in components:
 *   import { gsap, ScrollTrigger } from '@/lib/gsap'
 */

import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { Flip } from 'gsap/Flip'
import { Observer } from 'gsap/Observer'

// Register all plugins once
gsap.registerPlugin(ScrollTrigger, Flip, Observer)

// Global GSAP defaults for consistent feel
gsap.defaults({
  ease: 'power3.out',
  duration: 0.8,
})

// Export everything for use across the app
export { gsap, ScrollTrigger, Flip, Observer }
