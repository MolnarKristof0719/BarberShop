<template>
  <div class="flow-card active">
    <div class="flow-header">
      <h2>2. Barber valasztas</h2>
      <small v-if="selectedBarberMode === 'earliest'">Legkorábbi mód</small>
      <small v-else>{{ selectedBarberName || "Nincs kiválasztva" }}</small>
    </div>

    <div class="card-list">
      <button type="button" class="select-card" :class="{ selected: selectedBarberMode === 'earliest' }"
        @click="$emit('select-earliest-mode')">
        <span>Legkorabbi idopontok osszes barbernel</span>
        <i class="bi bi-clock-history"></i>
      </button>

      <div v-for="barber in activeBarbers" :key="barber.id" class="barber-card" :class="{
        inactive: !barber.isActive,
        selected:
          selectedBarberId === barber.id &&
          selectedBarberMode === 'specific',
      }" @click="onBarberClick(barber)">
        <div class="barber-main">
          <div class="avatar">
            <img v-if="barber.profilePicture" :src="resolveImage(barber.profilePicture)" :alt="barberName(barber)" />
            <span v-else>{{ initials(barberName(barber)) }}</span>
          </div>
          <div>
            <p class="barber-name mb-1">{{ barberName(barber) }}</p>
            <p class="barber-intro mb-0">
              {{ barber.introduction || "Barber" }}
              <span v-if="!barber.isActive" class="inactive-label"> (jelenleg nem aktív)</span>
            </p>
          </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm" @click.stop="$emit('open-reference', barber)">
          <i class="bi bi-image"></i>
          Képek
        </button>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-outline-secondary" @click="$emit('back')">Vissza</button>
      <button class="btn btn-dark" :disabled="!canProceedFromBarber" @click="$emit('next')">
        Tovább a naptárhoz </button>
    </div>
  </div>
</template>

<script>
import { resolveMediaUrl } from "@/utils/media";

export default {
  name: "BarbersComponent",
  props: {
    selectedBarberMode: { type: String, required: true },
    selectedBarberName: { type: String, default: "" },
    activeBarbers: { type: Array, required: true },
    selectedBarberId: { default: null },
    canProceedFromBarber: { type: Boolean, required: true },
  },
  emits: [
    "select-earliest-mode",
    "select-barber",
    "open-reference",
    "back",
    "next",
  ],
  methods: {
    onBarberClick(barber) {
      if (!barber?.isActive) {
        return;
      }
      this.$emit("select-barber", barber.id);
    },
    barberName(barber) {
      return barber?.user?.name || `Barber #${barber?.id || ""}`;
    },
    initials(name) {
      return String(name || "B")
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || "")
        .join("");
    },
    resolveImage(path) {
      return resolveMediaUrl(path);
    },
  },
};
</script>

<style scoped>
.barber-card.inactive {
  background: #f2f4f6;
  color: #8a949e;
  border-color: #dce2e8;
  cursor: not-allowed;
}

.inactive-label {
  font-style: italic;
  color: #8a949e;
}
</style>
