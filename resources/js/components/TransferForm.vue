<template>
  <form @submit.prevent="send">
    <h3>Transferir</h3>
    <input v-model="email" placeholder="Email do destinatário" />
    <input v-model.number="amount" type="number" step="0.01" placeholder="Valor" />
    <button :disabled="loading">{{ loading ? "Enviando..." : "Transferir" }}</button>
    <p v-if="error" style="color: #b00020">{{ error }}</p>
    <p v-if="success" style="color: #0a7d32">{{ success }}</p>
  </form>
</template>

<script setup>
import { ref } from "vue";
import api from "../api";

const emit = defineEmits(["done"]);
const email = ref("");
const amount = ref(null);
const loading = ref(false);
const error = ref("");
const success = ref("");

async function send() {
  loading.value = true;
  error.value = "";
  success.value = "";
  try {
    const { data } = await api.post("/transfer", { email: email.value, amount: amount.value });
    success.value = data.message || "Transferência realizada.";
    email.value = "";
    amount.value = null;
    emit("done");
  } catch (e) {
    error.value = e?.response?.data?.message || "Erro ao transferir.";
  } finally {
    loading.value = false;
  }
}
</script>
