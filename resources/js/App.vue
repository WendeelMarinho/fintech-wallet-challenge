<template>
  <div class="app-shell">
    <header v-if="showNav" class="top-nav">
      <div class="top-nav__inner">
        <router-link to="/" class="brand">Fintech Wallet</router-link>
        <nav class="nav-links">
          <router-link to="/">Início</router-link>
          <router-link to="/transactions">Histórico</router-link>
        </nav>
        <div class="nav-user">
          <span v-if="auth.user" :title="auth.user.email">{{ auth.user.name }}</span>
          <button type="button" class="btn btn-ghost" @click="logout">Sair</button>
        </div>
      </div>
    </header>
    <main class="main-area">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "./stores/authStore";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const showNav = computed(() => !["/login", "/register"].includes(route.path));

onMounted(async () => {
  if (auth.token && !auth.user) {
    try {
      await auth.me();
    } catch {
      await auth.logout();
      router.push("/login");
    }
  }
});

async function logout() {
  await auth.logout();
  router.push("/login");
}
</script>
