<template>
  <section>
    <h2>Histórico</h2>
    <form @submit.prevent="search" style="margin-bottom: 10px">
      <select v-model="type">
        <option value="">Todos</option>
        <option value="debit">debit</option>
        <option value="credit">credit</option>
      </select>
      <input v-model="dateFrom" type="date" />
      <input v-model="dateTo" type="date" />
      <button>Filtrar</button>
    </form>
    <TransactionList :items="transaction.items" />
    <div style="margin-top: 10px" v-if="transaction.pagination">
      <button :disabled="transaction.pagination.page <= 1" @click="change(transaction.pagination.page - 1)">
        Anterior
      </button>
      <span> Página {{ transaction.pagination.page }} de {{ transaction.pagination.lastPage }} </span>
      <button
        :disabled="transaction.pagination.page >= transaction.pagination.lastPage"
        @click="change(transaction.pagination.page + 1)"
      >
        Próxima
      </button>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useTransactionStore } from "../stores/transactionStore";
import TransactionList from "../components/TransactionList.vue";

const transaction = useTransactionStore();
const type = ref("");
const dateFrom = ref("");
const dateTo = ref("");

function params(page = 1) {
  return { page, type: type.value || undefined, date_from: dateFrom.value || undefined, date_to: dateTo.value || undefined };
}

async function search() {
  await transaction.fetch(params(1));
}

async function change(page) {
  await transaction.fetch(params(page));
}

onMounted(search);
</script>
