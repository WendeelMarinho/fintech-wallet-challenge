<template>
  <div>
    <h1 class="page-title">Início</h1>

    <div class="card balance-card">
      <p class="label">Saldo disponível</p>
      <p v-if="wallet.loading" class="amount amount--loading">Carregando…</p>
      <p v-else class="amount">{{ formattedBalance }}</p>
    </div>

    <TransferForm @done="reload" />

    <h2 class="section-title">Últimas transações</h2>
    <p class="loading-line" v-if="transaction.loading">Carregando extrato…</p>
    <TransactionList v-else :items="transaction.items" />
    <p style="margin-top: 1rem">
      <router-link to="/transactions">Ver histórico completo →</router-link>
    </p>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useWalletStore } from "../stores/walletStore";
import { useTransactionStore } from "../stores/transactionStore";
import { formatBRL } from "../utils/format";
import TransferForm from "../components/TransferForm.vue";
import TransactionList from "../components/TransactionList.vue";

const wallet = useWalletStore();
const transaction = useTransactionStore();
const formattedBalance = computed(() => formatBRL(wallet.balance));

async function reload() {
  await wallet.fetchBalance();
  await transaction.fetchLatest();
}

onMounted(reload);
</script>
