<template>
  <div class="card" style="margin-bottom: 1.25rem">
    <h2 class="section-title" style="margin-top: 0">Nova transferência</h2>
    <form class="form-stack" @submit.prevent="send">
      <div class="form-field">
        <label for="xfer-email">E-mail do destinatário</label>
        <input id="xfer-email" v-model="email" type="email" autocomplete="off" placeholder="destinatario@email.com" />
      </div>
      <div class="form-field">
        <label for="xfer-amount">Valor (R$)</label>
        <input
          id="xfer-amount"
          v-model.number="amount"
          type="number"
          step="0.01"
          min="0.01"
          placeholder="0,00"
        />
      </div>
      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? "Enviando…" : "Transferir" }}
      </button>
      <p v-if="error" class="alert alert--error">{{ error }}</p>
      <p v-if="success" class="alert alert--success">{{ success }}</p>
    </form>
  </div>
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
