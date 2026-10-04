import { defineStore } from "pinia";
import { ref } from "vue";
import { api, resetCsrf } from "../services/api";
import type { User } from "../types";
export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  const ready = ref(false);
  async function register(payload: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    const data = await api<{ user: User }>("/auth/register", {
      method: "POST",
      body: JSON.stringify(payload),
    });
    user.value = data.user;
    ready.value = true;
    resetCsrf();
  }
  function clear() {
    user.value = null;
    ready.value = false;
    resetCsrf();
  }
  return { user, ready, register, clear };
});
