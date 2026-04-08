<template>
  <div class="form-auth card">
    <h1 class="page-title" style="margin-bottom: 0.35rem">Criar conta</h1>
    <p class="subtitle">Você começa com saldo para testar transferências</p>
    <form class="form-stack" @submit.prevent="submit">
      <div class="form-field">
        <label for="reg-name">Nome</label>
        <input id="reg-name" v-model="name" type="text" autocomplete="name" placeholder="Seu nome" />
      </div>
      <div class="form-field">
        <label for="reg-email">E-mail</label>
        <input id="reg-email" v-model="email" type="email" autocomplete="email" placeholder="voce@email.com" />
      </div>
      <div class="form-field">
        <label for="reg-password">Senha</label>
        <input
          id="reg-password"
          v-model="password"
          type="password"
          autocomplete="new-password"
          placeholder="Mínimo seguro para dev"
        />
      </div>
      <button type="submit" class="btn btn-primary" :disabled="auth.loading">
        {{ auth.loading ? "Criando…" : "Registrar" }}
      </button>
      <p v-if="auth.error" class="alert alert--error">{{ auth.error }}</p>
    </form>
    <p class="link-row">Já tem conta? <router-link to="/login">Entrar</router-link></p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/authStore";

const auth = useAuthStore();
const router = useRouter();
const name = ref("");
const email = ref("");
const password = ref("");

async function submit() {
  await auth.register({ name: name.value, email: email.value, password: password.value });
  if (auth.token) router.push("/");
}
</script>
