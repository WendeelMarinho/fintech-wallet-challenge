<template>
  <div>
    <h1 class="page-title">Histórico</h1>

    <div class="card" style="margin-bottom: 1.25rem">
      <form class="filters-row" @submit.prevent="search">
        <div class="form-field form-field--grow">
          <label for="filter-type">Tipo</label>
          <select id="filter-type" v-model="type">
            <option value="">Todos</option>
            <option value="debit">Enviado (débito)</option>
            <option value="credit">Recebido (crédito)</option>
          </select>
        </div>
        <div class="form-field">
          <label for="filter-from">De</label>
          <input id="filter-from" v-model="dateFrom" type="date" />
        </div>
        <div class="form-field">
          <label for="filter-to">Até</label>
          <input id="filter-to" v-model="dateTo" type="date" />
        </div>
        <button type="submit" class="btn btn-primary" :disabled="transaction.loading">
          {{ transaction.loading ? "…" : "Filtrar" }}
        </button>
      </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden">
      <p class="loading-line" style="padding: 1rem; margin: 0" v-if="transaction.loading">Carregando…</p>
      <TransactionList v-else :items="transaction.items" />
    </div>

    <div class="pagination-bar" v-if="transaction.pagination && !transaction.loading">
      <button
        type="button"
        class="btn btn-secondary"
        :disabled="transaction.pagination.page <= 1"
        @click="change(transaction.pagination.page - 1)"
      >
        Anterior
      </button>
      <span class="meta">
        Página {{ transaction.pagination.page }} de {{ transaction.pagination.lastPage }}
      </span>
      <button
        type="button"
        class="btn btn-secondary"
        :disabled="transaction.pagination.page >= transaction.pagination.lastPage"
        @click="change(transaction.pagination.page + 1)"
      >
        Próxima
      </button>
    </div>
  </div>
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
  return {
    page,
    type: type.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  };
}

async function search() {
  await transaction.fetch(params(1));
}

async function change(page) {
  await transaction.fetch(params(page));
}

onMounted(search);
</script>
