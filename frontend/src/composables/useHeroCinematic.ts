import { nextTick, onBeforeUnmount, onMounted, ref, type Ref } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

type ElementRef = Ref<HTMLElement | null>;
interface HeroElements {
  stage: ElementRef;
  video: Ref<HTMLVideoElement | null>;
}

const VIDEO_URL = `${import.meta.env.BASE_URL}videos/scan-save-intro.mp4`;
const clamp = gsap.utils.clamp(0, 1);

/** Scrolling advances only the background video; foreground content stays visible and usable. */
export function useHeroCinematic(elements: HeroElements) {
  const enabled = ref(false);
  const reduced = window.matchMedia("(prefers-reduced-motion: reduce)");
  const touch = window.matchMedia("(pointer: coarse)");
  const playhead = { progress: 0 };
  let context: gsap.Context | undefined;
  let tween: gsap.core.Tween | undefined;
  let trigger: ScrollTrigger | undefined;
  let hintLoop: gsap.core.Timeline | undefined;
  let hint: HTMLElement | null = null;
  let duration = 0;
  let distance = 0;
  let viewportHeight = 0;
  let viewportWidth = 0;
  let frameHeight = 0;
  let headerHeight = 0;
  let desiredTime = 0;
  let seekFrame = 0;
  let resizeFrame = 0;
  let loadTimer: ReturnType<typeof setTimeout> | undefined;
  let seekTimer: ReturnType<typeof setTimeout> | undefined;
  let mounted = false;
  let preparing = false;
  let disabled = false;
  let refreshing = false;
  let restoring = false;
  let anchor = { started: false, progress: 0, after: 0, before: 0 };
  const cleanups: Array<() => void> = [];

  function listen(target: EventTarget, event: string, callback: EventListener) {
    target.addEventListener(event, callback);
    cleanups.push(() => target.removeEventListener(event, callback));
  }

  function measure() {
    const stage = elements.stage.value;
    if (!stage) return;
    const header = stage.closest("main")?.previousElementSibling;
    headerHeight = header instanceof HTMLElement ? header.offsetHeight : 0;
    frameHeight = Math.max(1, window.innerHeight - headerHeight);
    stage.style.setProperty("--hero-viewport-height", `${frameHeight}px`);
    // About 3.5 desktop screens / 2 phone screens for this 31.6 s asset, not the old intro budget.
    const screens =
      window.innerWidth < 768
        ? gsap.utils.clamp(1.7, 2.3, duration / 15.5)
        : gsap.utils.clamp(2.8, 3.6, duration / 9);
    distance = Math.round(viewportHeight * screens);
  }

  function seek() {
    seekFrame = 0;
    const video = elements.video.value;
    if (
      !video ||
      disabled ||
      video.readyState < 2 ||
      video.seeking ||
      !duration
    )
      return;
    const target = Math.max(0, Math.min(duration - 0.001, desiredTime));
    if (
      video.currentTime === target ||
      (target > 0 && Math.abs(video.currentTime - target) < 1 / 48)
    )
      return;
    try {
      video.currentTime = target;
      watchSeek();
    } catch {
      // The next readiness event retries without blocking access to the page.
    }
  }

  function watchSeek() {
    clearTimeout(seekTimer);
    if (document.hidden) return;
    seekTimer = setTimeout(() => {
      if (!document.hidden && elements.video.value?.seeking) void disable();
    }, 6000);
  }

  function queueSeek() {
    if (!seekFrame && !disabled) seekFrame = requestAnimationFrame(seek);
  }

  function render() {
    if (!enabled.value || !trigger || refreshing) return;
    rememberPosition();
    const p =
      trigger.progress >= 0.9999
        ? 1
        : trigger.progress <= 0
          ? 0
          : clamp(playhead.progress);
    const videoProgress = clamp(p / 0.95);
    desiredTime = videoProgress * duration;
    queueSeek();

    hintLoop?.paused(!trigger.isActive && trigger.progress > 0);
  }

  function prepareHint() {
    if (!hint) return;
    hint.dataset.input = touch.matches ? "touch" : "scroll";
    const arrow = hint.querySelector(".hero-scroll-hint-arrow");
    hintLoop?.kill();
    hintLoop = gsap
      .timeline({ repeat: -1, repeatDelay: 0.7 })
      .fromTo(
        arrow,
        { y: touch.matches ? 5 : -5, opacity: 0.35 },
        { y: 0, opacity: 1, duration: 0.45, ease: "power2.out" },
      )
      .to(arrow, {
        y: touch.matches ? -5 : 5,
        opacity: 0.35,
        duration: 0.85,
        delay: 0.25,
        ease: "power1.in",
      });
  }

  function beforeRefresh() {
    refreshing = true;
    measure();
  }

  function afterRefresh() {
    refreshing = false;
    render();
  }

  async function ready() {
    const video = elements.video.value;
    const stage = elements.stage.value;
    if (
      disabled ||
      preparing ||
      trigger ||
      !mounted ||
      !stage ||
      !video ||
      video.readyState < 2 ||
      !Number.isFinite(video.duration) ||
      video.duration <= 0
    )
      return;
    // A late download must not insert a long sequence behind someone already reading the next section.
    const naturalStart =
      window.scrollY +
      stage.getBoundingClientRect().bottom -
      window.innerHeight;
    if (window.scrollY > Math.max(0, naturalStart) + 24) {
      void disable();
      return;
    }
    preparing = true;
    clearTimeout(loadTimer);
    duration = video.duration;
    viewportWidth = window.innerWidth;
    viewportHeight = window.innerHeight;
    measure();
    enabled.value = true;
    await nextTick();
    if (!mounted || disabled) return;
    hint = stage.querySelector(".hero-scroll-hint");
    context = gsap.context(() => {
      prepareHint();
      tween = gsap.to(playhead, {
        progress: 1,
        ease: "none",
        onUpdate: render,
        scrollTrigger: {
          trigger: stage,
          pin: stage,
          // A tall mobile hero is read normally before its lower viewport becomes the cinema.
          start: "bottom bottom",
          end: () => `+=${distance}`,
          scrub: 0.2,
          invalidateOnRefresh: true,
          anticipatePin: 1,
          refreshPriority: 1,
          onUpdate: render,
        },
      });
      trigger = tween.scrollTrigger;
    }, stage);
    ScrollTrigger.addEventListener("refreshInit", beforeRefresh);
    ScrollTrigger.addEventListener("refresh", afterRefresh);
    ScrollTrigger.refresh();
    playhead.progress = tween?.scrollTrigger?.progress ?? 0;
    render();
    preparing = false;
  }

  function destroy() {
    ScrollTrigger.removeEventListener("refreshInit", beforeRefresh);
    ScrollTrigger.removeEventListener("refresh", afterRefresh);
    hintLoop?.kill();
    hintLoop = undefined;
    context?.revert();
    context = undefined;
    trigger = undefined;
    tween = undefined;
    cancelAnimationFrame(seekFrame);
    seekFrame = 0;
  }

  async function disable() {
    if (disabled) return;
    disabled = true;
    const scroll = window.scrollY;
    const start = trigger?.start;
    const progress = trigger?.progress ?? 0;
    const removed = distance * progress;
    destroy();
    clearTimeout(loadTimer);
    clearTimeout(seekTimer);
    enabled.value = false;
    const video = elements.video.value;
    if (video) {
      video.pause();
      video.removeAttribute("src");
      video.load();
    }
    await nextTick();
    if (!mounted) return;
    if (start !== undefined)
      window.scrollTo({
        top: Math.max(0, scroll - removed),
        behavior: "instant",
      });
    ScrollTrigger.refresh();
  }

  function rememberPosition() {
    if (
      !trigger ||
      refreshing ||
      restoring ||
      window.innerWidth !== viewportWidth ||
      Math.abs(window.innerHeight - viewportHeight) >= 100
    )
      return;
    anchor = {
      started: window.scrollY >= trigger.start,
      progress: trigger.progress,
      after: Math.max(0, window.scrollY - trigger.end),
      before: window.scrollY,
    };
  }

  function resized() {
    if (!enabled.value) {
      measure();
      return;
    }
    if (resizeFrame) return;
    resizeFrame = requestAnimationFrame(() => {
      resizeFrame = 0;
      const saved = { ...anchor };
      restoring = true;
      if (
        window.innerWidth !== viewportWidth ||
        Math.abs(window.innerHeight - viewportHeight) >= 100
      ) {
        viewportWidth = window.innerWidth;
        viewportHeight = window.innerHeight;
      }
      ScrollTrigger.refresh();
      if (trigger) {
        window.scrollTo({
          top: saved.started
            ? trigger.start + saved.progress * distance + saved.after
            : Math.min(saved.before, trigger.start),
          behavior: "instant",
        });
        ScrollTrigger.update();
        trigger.getTween()?.progress(1);
        render();
      }
      restoring = false;
      rememberPosition();
    });
  }

  onMounted(() => {
    mounted = true;
    viewportWidth = window.innerWidth;
    viewportHeight = window.innerHeight;
    measure();
    listen(window, "resize", resized);
    const header =
      elements.stage.value?.closest("main")?.previousElementSibling;
    if (header) {
      const observer = new ResizeObserver(resized);
      observer.observe(header);
      cleanups.push(() => observer.disconnect());
    }
    const video = elements.video.value;
    if (reduced.matches || !video || !video.canPlayType("video/mp4")) return;
    listen(reduced, "change", () => {
      if (reduced.matches) void disable();
    });
    listen(touch, "change", () => {
      if (enabled.value) {
        prepareHint();
        render();
      }
    });
    listen(window, "scroll", rememberPosition);
    listen(video, "loadedmetadata", () => {
      void ready();
    });
    listen(video, "loadeddata", () => {
      void ready();
    });
    listen(video, "canplay", () => {
      void ready();
      queueSeek();
    });
    listen(video, "seeked", () => {
      clearTimeout(seekTimer);
      queueSeek();
      render();
    });
    listen(video, "error", () => {
      void disable();
    });
    video.muted = true;
    video.defaultMuted = true;
    listen(document, "visibilitychange", () => {
      clearTimeout(seekTimer);
      if (!document.hidden) {
        if (video.seeking) watchSeek();
        queueSeek();
      }
    });
    video.src = VIDEO_URL;
    video.load();
    loadTimer = setTimeout(() => {
      if (!enabled.value) void disable();
    }, 12000);
    void ready();
  });

  onBeforeUnmount(() => {
    mounted = false;
    disabled = true;
    clearTimeout(loadTimer);
    clearTimeout(seekTimer);
    cancelAnimationFrame(resizeFrame);
    cleanups.forEach((cleanup) => cleanup());
    destroy();
    const video = elements.video.value;
    if (video) {
      video.pause();
      video.removeAttribute("src");
      video.load();
    }
  });

  return { enabled };
}
