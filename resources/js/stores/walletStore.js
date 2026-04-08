import { defineStore } from "pinia";
import api from "../api";

export const useWalletStore = defineStore("wallet", {
  state: () => ({ balance: "0.00", loading: false }),
  actions: {
    async fetchBalance() {
      this.loading = true;
      try {
        const { data } = await api.get("/wallet");
        this.balance = data.data.balance;
      } finally {
        this.loading = false;
      }
    },
  },
});
