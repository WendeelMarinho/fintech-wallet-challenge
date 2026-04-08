<template>
  <div class="form-auth card">
    <h1 class="page-title" style="margin-bottom: 0.35rem">Entrar</h1>
    <p class="subtitle">Acesse sua carteira</p>
    <form class="form-stack" @submit.prevent="submit">
      <div class="form-field">
        <label for="login-email">E-mail</label>
        <input id="login-email" v-model="email" type="email" autocomplete="email" placeholder="voce@email.com" />
      </div>
      <div class="form-field">
        <label for="login-password">Senha</label>
        <input
          id="login-password"
          v-model="password"
          type="password"
          autocomplete="current-password"
          placeholder="••••••••"
        />
      </div>
      <button type="submit" class="btn btn-primary" :disabled="auth.loading">
        {{ auth.loading ? "Entrando…" : "Entrar" }}
      </button>
      <p v-if="auth.error" class="alert alert--error">{{ auth.error }}</p>
    </form>
    <p class="link-row">Não tem conta? <router-link to="/register">Criar conta</router-link></p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/authStore";

const auth = useAuthStore();
const router = useRouter();
const email = ref("");
const password = ref("");

async function submit() {
  await auth.login({ email: email.value, password: password.value });
  if (auth.token) router.push("/");
}
</script>
