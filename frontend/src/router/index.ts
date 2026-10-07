import { createRouter, createWebHistory } from "vue-router";
const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(to, _from, saved) {
    if (saved) return saved;
    if (to.hash)
      return {
        el: to.hash,
        top: 95,
        behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches
          ? "instant"
          : "smooth",
      };
    return { top: 0 };
  },
  routes: [
    { path: "/", component: () => import("../views/HomeView.vue") },
    ...["login", "register"].map(
      (mode) => ({
        path: `/${mode}`,
        component: () => import("../views/auth/AuthView.vue"),
        props: { mode },
        meta: { guest: true },
      }),
    ),
  ],
});
export default router;
