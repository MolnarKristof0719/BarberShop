<template>
  <div class="flow-card active">
    <div class="flow-header">
      <h2>1. Szolgaltatasok</h2>
      <small>{{ selectedServiceIds.length }} kivalasztva</small>
    </div>

    <div v-if="services.length" class="card-list">
      <button
        v-for="service in services"
        :key="service.id"
        type="button"
        class="select-card"
        :class="{ selected: selectedServiceIds.includes(service.id) }"
        @click="$emit('toggle-service', service.id)"
      >
        <span>{{ service.service }}</span>
        <i
          class="bi"
          :class="
            selectedServiceIds.includes(service.id)
              ? 'bi-check-circle-fill'
              : 'bi-plus-circle'
          "
        ></i>
      </button>
    </div>
    <p v-else class="empty-state">Nincs elerheto szolgaltatas.</p>

    <div class="actions">
      <button
        class="btn btn-dark"
        :disabled="!selectedServiceIds.length"
        @click="$emit('next')"
      >
        Tovabb a barberekhez
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "ServicesComponent",
  props: {
    services: { type: Array, required: true },
    selectedServiceIds: { type: Array, required: true },
  },
  emits: ["toggle-service", "next"],
};
</script>
