<template>
  <section>
    <h2>Dashboard</h2>
    <p><strong>Saldo:</strong> {{ wallet.balance }}</p>
    <TransferForm @done="reload" />
    <h3>Últimas 5 transações</h3>
    <TransactionList :items="transaction.items" />
    <p><router-link to="/transactions">Ver histórico completo</router-link></p>
  </section>
</template>

<script setup>
import { onMounted } from "vue";
import { useWalletStore } from "../stores/walletStore";
import { useTransactionStore } from "../stores/transactionStore";
import TransferForm from "../components/TransferForm.vue";
import TransactionList from "../components/TransactionList.vue";

const wallet = useWalletStore();
const transaction = useTransactionStore();

async function reload() {
  await wallet.fetchBalance();
  await transaction.fetchLatest();
}

onMounted(reload);
</script>
