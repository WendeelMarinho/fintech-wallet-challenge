import { defineStore } from "pinia";
import api from "../api";

export const useAuthStore = defineStore("auth", {
  state: () => ({ user: null, token: localStorage.getItem("token"), loading: false, error: null }),
  actions: {
    async register(payload) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await api.post("/register", payload);
        this.token = data.data.token;
        this.user = data.data.user;
        localStorage.setItem("token", this.token);
      } catch (e) {
        this.error = e?.response?.data?.message || "Erro ao registrar.";
      } finally {
        this.loading = false;
      }
    },
    async login(payload) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await api.post("/login", payload);
        this.token = data.data.token;
        this.user = data.data.user;
        localStorage.setItem("token", this.token);
      } catch (e) {
        this.error = e?.response?.data?.message || "Erro ao entrar.";
      } finally {
        this.loading = false;
      }
    },
    async me() {
      const { data } = await api.get("/me");
      this.user = data.data;
    },
    async logout() {
      try {
        await api.post("/logout");
      } catch (_e) {}
      this.user = null;
      this.token = null;
      localStorage.removeItem("token");
    },
  },
});
