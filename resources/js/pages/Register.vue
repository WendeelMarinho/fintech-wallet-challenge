<template>
  <section>
    <h2>Registro</h2>
    <form @submit.prevent="submit">
      <input v-model="name" placeholder="Nome" />
      <input v-model="email" placeholder="Email" />
      <input v-model="password" type="password" placeholder="Senha" />
      <button :disabled="auth.loading">{{ auth.loading ? "Salvando..." : "Registrar" }}</button>
      <p v-if="auth.error" style="color: #b00020">{{ auth.error }}</p>
    </form>
    <router-link to="/login">Já tenho conta</router-link>
  </section>
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
