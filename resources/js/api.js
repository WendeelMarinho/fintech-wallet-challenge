import axios from "axios";

// Em dev, Vite faz proxy de "/api". Em produção (SPA em outro host), defina VITE_API_URL no build.
const baseURL = import.meta.env.VITE_API_URL?.replace(/\/$/, "") || "/api";

const api = axios.create({ baseURL });

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export default api;
