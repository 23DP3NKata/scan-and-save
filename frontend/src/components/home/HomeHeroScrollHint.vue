<template>
  <div class="hero-scroll-hint" aria-hidden="true">
    <div class="hero-scroll-hint-content">
      <span class="hero-scroll-hint-scroll">Ritini, lai skatītos</span>
      <span class="hero-scroll-hint-touch">Velc uz augšu, lai skatītos</span>
      <span class="hero-scroll-hint-arrow">
        <svg
          width="18"
          height="22"
          viewBox="0 0 24 24"
          fill="none"
          aria-hidden="true"
          focusable="false"
        >
          <path
            d="M12 4v16m-6-6 6 6 6-6"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </span>
    </div>
  </div>
</template>

<style scoped>
.hero-scroll-hint {
  width: fit-content;
  max-width: min(100%, 335px);
  margin-top: 24px;
  color: var(--muted, #69717b);
  pointer-events: none;
  user-select: none;
}

.hero-scroll-hint-content {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: var(--font-weight-medium);
  line-height: 1.5;
  letter-spacing: 0.01em;
}

.hero-scroll-hint-touch {
  display: none;
}

.hero-scroll-hint-arrow {
  display: inline-flex;
  flex: 0 0 18px;
  align-items: center;
  justify-content: center;
  color: var(--navy, #142537);
}

.hero-scroll-hint-arrow svg {
  display: block;
  transform-origin: center;
}

.hero-scroll-hint[data-input='touch'] .hero-scroll-hint-scroll {
  display: none;
}

.hero-scroll-hint[data-input='touch'] .hero-scroll-hint-touch {
  display: inline;
}

.hero-scroll-hint[data-input='touch'] .hero-scroll-hint-arrow svg {
  transform: rotate(180deg);
}
</style>
