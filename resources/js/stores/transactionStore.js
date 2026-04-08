import { defineStore } from "pinia";
import api from "../api";

export const useTransactionStore = defineStore("transaction", {
  state: () => ({ items: [], pagination: null, loading: false }),
  actions: {
    async fetch(params = {}) {
      this.loading = true;
      try {
        const { data } = await api.get("/transactions", { params });
        this.items = data.data.data;
        this.pagination = {
          page: data.data.current_page,
          lastPage: data.data.last_page,
        };
      } finally {
        this.loading = false;
      }
    },
    async fetchLatest() {
      this.loading = true;
      try {
        const { data } = await api.get("/transactions/latest");
        this.items = Array.isArray(data.data) ? data.data : [];
      } finally {
        this.loading = false;
      }
    },
  },
});
