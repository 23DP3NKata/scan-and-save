import { onMounted, onUnmounted, type Ref } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

/** MatchMedia reverts all animations on preference changes and on unmount. */
export function useHomeMotion(root: Ref<HTMLElement | null>) {
  let media: gsap.MatchMedia | undefined
  onMounted(() => {
    if (!root.value) return
    media = gsap.matchMedia()
    media.add('(prefers-reduced-motion: no-preference)', () => {
      const ctx = gsap.context(() => {
        gsap.utils.toArray<HTMLElement>('.reveal').forEach(el => {
          gsap.from(el, { y: 24, opacity: 0, duration: .7, ease: 'power2.out', scrollTrigger: { trigger: el, start: 'top 94%', once: true } })
        })
        gsap.from('.step-track > div', { scaleX: 0, transformOrigin: 'left', ease: 'none', scrollTrigger: { trigger: '.steps', start: 'top 85%', end: 'bottom 65%', scrub: .6 } })
        gsap.from('.notification', { y: 28, opacity: 0, duration: .8, scrollTrigger: { trigger: '.notification', start: 'top 90%', once: true } })
      }, root.value!)
      return () => ctx.revert()
    })
    media.add('(min-width: 1024px) and (prefers-reduced-motion: no-preference)', () => {
      const ctx = gsap.context(() => {
        gsap.from('.story-file', { x: -55, rotation: -6, scrollTrigger: { trigger: '.story-art', start: 'top 85%', end: 'center 55%', scrub: .7 } })
        gsap.to('.story-receipt', { x: 25, rotation: -3, scrollTrigger: { trigger: '.story-art', start: 'top 85%', end: 'center 55%', scrub: .7 } })
      }, root.value!)
      return () => ctx.revert()
    })
  })
  onUnmounted(() => media?.revert())
}
