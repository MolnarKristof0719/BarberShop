<template>
  <div class="flow-card active">
    <div class="flow-header">
      <h2>4. Foglalas veglegesites</h2>
      <small>Ellenorizd az adatokat</small>
    </div>

    <ul class="summary-list">
      <li><strong>Szolgaltatasok:</strong> {{ selectedServiceNames.join(", ") }}</li>
      <li><strong>Barber:</strong> {{ selectedBarberName }}</li>
      <li><strong>Datum:</strong> {{ selectedDate }}</li>
      <li><strong>Idopont:</strong> {{ selectedTime }}</li>
    </ul>

    <div class="actions">
      <button class="btn btn-outline-secondary" @click="$emit('back')">Vissza</button>
      <button class="btn btn-success" :disabled="submitLoading" @click="$emit('submit')">
        {{ submitLoading ? "Kuldese..." : "Foglalas kuldese" }}
      </button>
    </div>

    <p v-if="submitError" class="text-danger mt-3 mb-0">{{ submitError }}</p>
  </div>
</template>

<script>
export default {
  name: "ReviewComponent",
  props: {
    selectedServiceNames: { type: Array, required: true },
    selectedBarberName: { type: String, default: "" },
    selectedDate: { type: String, default: "" },
    selectedTime: { type: String, default: "" },
    submitLoading: { type: Boolean, required: true },
    submitError: { type: String, default: "" },
  },
  emits: ["back", "submit"],
};
</script>
