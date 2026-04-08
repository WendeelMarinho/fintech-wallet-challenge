<template>
  <div v-if="!items.length" class="empty-state">Nenhuma movimentação nesta lista.</div>
  <div v-else class="table-wrap">
    <table class="tx-table">
      <thead>
        <tr>
          <th>Data</th>
          <th>Tipo</th>
          <th>Valor</th>
          <th>Contraparte</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in items" :key="t.id">
          <td>{{ formatDateTime(t.created_at) }}</td>
          <td>
            <span :class="['badge', t.type === 'debit' ? 'badge--debit' : 'badge--credit']">
              {{ t.type === "debit" ? "Enviado" : "Recebido" }}
            </span>
          </td>
          <td :class="t.type === 'debit' ? 'amount-debit' : 'amount-credit'">
            {{ t.type === "debit" ? "−" : "+" }} {{ formatBRL(t.amount) }}
          </td>
          <td>{{ counterpartyLabel(t) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { formatBRL, formatDateTime } from "../utils/format";

defineProps({ items: { type: Array, default: () => [] } });

function counterpartyLabel(t) {
  if (t.type === "debit") {
    return t.receiver?.email ?? (t.receiver_id != null ? `#${t.receiver_id}` : "—");
  }
  return t.sender?.email ?? (t.sender_id != null ? `#${t.sender_id}` : "—");
}
</script>
