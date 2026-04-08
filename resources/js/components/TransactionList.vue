<template>
  <table border="1" cellpadding="6" cellspacing="0" width="100%">
    <thead>
      <tr>
        <th>Data</th>
        <th>Tipo</th>
        <th>Valor</th>
        <th>Usuário envolvido</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="t in items" :key="t.id">
        <td>{{ new Date(t.created_at).toLocaleString() }}</td>
        <td>{{ t.type }}</td>
        <td>{{ t.amount }}</td>
        <td>{{ t.type === "debit" ? t.receiver_id : t.sender_id }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
defineProps({ items: { type: Array, default: () => [] } });
</script>
