<script setup lang="ts">
import { ref } from "vue";
import HomeIcon from "./HomeIcon.vue";
import HomeHeroScrollHint from "./HomeHeroScrollHint.vue";
import { useHeroCinematic } from "../../composables/useHeroCinematic";

defineEmits<{ start: [] }>();
const stage = ref<HTMLElement | null>(null);
const video = ref<HTMLVideoElement | null>(null);
const poster = `${import.meta.env.BASE_URL}videos/scan-save-intro-poster.webp`;
const { enabled } = useHeroCinematic({ stage, video });
</script>

<template>
  <section ref="stage" class="hero" aria-labelledby="hero-title">
    <video
      ref="video"
      class="hero-background-video"
      :poster="poster"
      muted
      playsinline
      preload="auto"
      disablepictureinpicture
      disableremoteplayback
      tabindex="-1"
      aria-hidden="true"
    />
    <div class="hero-foreground">
      <div class="hero-overlay" aria-hidden="true" />
      <div class="hero-content shell">
        <div class="hero-copy">
          <div class="eyebrow">
            <span class="tiny-dot" /> MAZĀK PAPĪRU. VAIRĀK MIERA.
          </div>
          <h1 id="hero-title">
            <span>Tavi pirkumi.</span>
            <span>Tavi dokumenti.</span>
            <span class="blue-text">Vienmēr ar tevi.</span>
          </h1>
          <p class="hero-description">
            Čeki, garantijas un svarīgie pirkumu dokumenti.<br
              class="desktop-break"
            />
            Vienā vietā. Tieši tad, kad tos vajag.
          </p>
          <div class="hero-buttons">
            <v-btn
              class="button button-blue"
              variant="flat"
              @click="$emit('start')"
            >
              Sākt bez maksas <HomeIcon name="arrow" :size="18" />
            </v-btn>
            <a class="text-link" href="#ka-tas-darbojas"
              >Uzzināt vairāk <span aria-hidden="true">↗</span></a
            >
          </div>
          <div class="hero-note">
            <HomeIcon name="check" :size="15" /> Mazāk meklēšanas
            <span /> Vairāk pārliecības
          </div>
          <HomeHeroScrollHint v-show="enabled" />
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero {
  --header-height: 89px;
  position: relative;
  isolation: isolate;
  min-height: calc(100svh - var(--header-height));
  min-height: var(--hero-viewport-height, calc(100svh - var(--header-height)));
  display: flex;
  overflow: clip;
  overflow-anchor: none;
  background: var(--paper);
}
.hero-background-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  z-index: 0;
  border-radius: 0;
  pointer-events: none;
}
.hero-foreground {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  width: 100%;
}
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(250, 251, 249, 0.96) 0%,
    rgba(250, 251, 249, 0.9) 27%,
    rgba(250, 251, 249, 0.67) 46%,
    rgba(250, 251, 249, 0.12) 72%,
    rgba(250, 251, 249, 0) 100%
  );
  pointer-events: none;
}
.hero-content {
  position: relative;
  padding-block: clamp(42px, 7vh, 90px);
}
.hero-copy {
  max-width: 640px;
}
.hero-copy .eyebrow {
  display: flex;
  align-items: center;
  gap: 9px;
  font-size: 9px;
  margin-bottom: 24px;
}
.hero h1 {
  font-size: clamp(43px, 4.65vw, 71px);
  font-weight: var(--font-weight-heading);
  letter-spacing: var(--tracking-hero);
  line-height: 1.17;
}
.hero h1 > span {
  display: block;
}
.hero-description {
  color: #465261;
  font-size: 14px;
  line-height: 1.9;
  margin-top: 26px;
}
.hero-buttons {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 28px;
  margin-top: 31px;
}
.hero-note {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 10px;
  color: #56616b;
  margin-top: 21px;
}
.hero-note > span {
  height: 3px;
  width: 3px;
  background: #7b8792;
  border-radius: 50%;
  margin-inline: 3px;
}
.hero-note svg {
  color: var(--blue);
}
.hero-copy :deep(.hero-scroll-hint) {
  color: #465261;
}
@media (max-width: 900px) {
  .hero {
    --header-height: 75px;
  }
  .hero-overlay {
    background: linear-gradient(
      90deg,
      rgba(250, 251, 249, 0.94),
      rgba(250, 251, 249, 0.86) 45%,
      rgba(250, 251, 249, 0.24)
    );
  }
  .hero h1 {
    font-size: clamp(43px, 6vw, 59px);
  }
}
@media (max-width: 650px) {
  .hero {
    --header-height: 71px;
  }
  .hero-content {
    padding-block: 40px;
  }
  .hero-overlay {
    background: linear-gradient(
      90deg,
      rgba(250, 251, 249, 0.89),
      rgba(250, 251, 249, 0.74) 65%,
      rgba(250, 251, 249, 0.45)
    );
  }
  .hero-copy .eyebrow {
    font-size: 8px;
    letter-spacing: 1.3px;
    margin-bottom: 20px;
  }
  .hero h1 {
    font-size: clamp(37px, 9.8vw, 59px);
    line-height: 1.2;
  }
  .hero-description {
    font-size: 12px;
    line-height: 1.85;
    margin-top: 21px;
  }
  .hero-buttons {
    margin-top: 23px;
    gap: 24px;
  }
  .hero-buttons .button {
    height: 48px;
    padding-inline: 20px;
    font-size: 12px;
  }
  .hero-buttons .text-link {
    font-size: 12px;
  }
  .hero-note {
    font-size: 9px;
    margin-top: 16px;
  }
}
</style>
