import { createRouter, createWebHistory } from "vue-router";
import Login from "../pages/Login.vue";
import Register from "../pages/Register.vue";
import Dashboard from "../pages/Dashboard.vue";
import Transactions from "../pages/Transactions.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/login", component: Login },
    { path: "/register", component: Register },
    { path: "/", component: Dashboard },
    { path: "/transactions", component: Transactions },
  ],
});

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem("token");
  if (!token && !["/login", "/register"].includes(to.path)) return next("/login");
  if (token && ["/login", "/register"].includes(to.path)) return next("/");
  next();
});

export default router;
