<template>
  <div class="flow-card active">
    <div class="flow-header">
      <h2>1. Szolgáltatások</h2>
      <small>{{ selectedServiceIds.length }} kiválasztva</small>
    </div>

    <div v-if="services.length" class="card-list col-12" >
      <button
        v-for="service in services"
        :key="service.id"
        type="button"
        class="select-card"
        :class="{
          selected: selectedServiceIds.includes(service.id),
          'disabled-service':
            disabledServiceIds.includes(service.id) &&
            !selectedServiceIds.includes(service.id),
        }"
        :disabled="
          disabledServiceIds.includes(service.id) &&
          !selectedServiceIds.includes(service.id)
        "
        @click="$emit('toggle-service', service.id)"
      >
        <span class="service-info">
          <span class="service-name">{{ service.service }}</span>
          <span class="service-price">{{ service.price }} Ft</span>
        </span>
        
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
    <p v-else class="empty-state">Nincs elérhető szolgáltatás.</p>

    <div class="actions">
      <button
        class="btn btn-dark"
        :disabled="!selectedServiceIds.length"
        @click="$emit('next')"
      >
        Tovább a barberekhez
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
    disabledServiceIds: { type: Array, default: () => [] },
  },
  emits: ["toggle-service", "next"],
};
</script>
