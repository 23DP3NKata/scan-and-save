import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";
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
    {
      path: "/connection-error",
      component: () => import("../views/ConnectionError.vue"),
    },
    {
      path: "/:pathMatch(.*)*",
      component: () => import("../views/NotFoundView.vue"),
    },
  ],
});
router.beforeEach(async (to) => {
  if (!to.meta.auth && !to.meta.guest) return;
  const auth = useAuthStore();
  try {
    await auth.fetchUser(true);
  } catch {
    return { path: "/connection-error", query: { next: to.fullPath } };
  }
  if (to.meta.auth && !auth.user)
    return { path: "/login", query: { redirect: to.fullPath } };
  if (to.meta.admin && auth.user?.role !== 1) return "/app";
  if (to.meta.guest && auth.user) return "/app";
});
router.afterEach((to) => {
  document.title = `${to.meta.title || "Tavi dokumenti. Vienmēr ar tevi."} · Scan & Save`;
});
export default router;
