<template>
  <section>
    <h2>Login</h2>
    <form @submit.prevent="submit">
      <input v-model="email" placeholder="Email" />
      <input v-model="password" type="password" placeholder="Senha" />
      <button :disabled="auth.loading">{{ auth.loading ? "Entrando..." : "Entrar" }}</button>
      <p v-if="auth.error" style="color: #b00020">{{ auth.error }}</p>
    </form>
    <router-link to="/register">Criar conta</router-link>
  </section>
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
