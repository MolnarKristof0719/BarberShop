<template>
  <div class="flow-card active">
    <div class="flow-header">
      <h2>3. Dátum és idősáv</h2>
      <small v-if="selectedDate && selectedTime">{{ selectedDate }} {{ selectedTime }}</small>
      <small v-else>Válassz időpontot</small>
    </div>

    <div v-if="selectedBarberMode === 'earliest'">
      <button class="btn btn-dark mb-3" :disabled="earliestLoading" @click="$emit('load-earliest-options')">
        {{ earliestLoading ? "Keresés..." : "Legkorábbi időpontok betöltése" }}
      </button>

      <div v-if="earliestOptions.length" class="card-list">
        <button
          v-for="option in earliestOptions"
          :key="`${option.barberId}-${option.appointmentDate}-${option.appointmentTime}`"
          type="button"
          class="select-card"
          :class="{
            selected:
              selectedDate === option.appointmentDate &&
              selectedTime === option.appointmentTime &&
              selectedBarberId === option.barberId,
          }"
          @click="$emit('pick-earliest-option', option)"
        >
          <span>
            {{ option.appointmentDate }} {{ option.appointmentTime }} - {{ option.barberName }}
          </span>
          <i class="bi bi-check2"></i>
        </button>
      </div>
      <p v-else class="empty-state">Nyomd meg a gombot a legkorábbi opciókhoz.</p>
    </div>

    <div v-else>
      <div class="calendar-tools">
        <button class="btn btn-outline-secondary btn-sm" @click="$emit('change-month', -1)">Előző hónap</button>
        <strong>{{ monthLabel }}</strong>
        <button class="btn btn-outline-secondary btn-sm" @click="$emit('change-month', 1)">Következő hónap</button>
      </div>

      <div class="calendar-grid mt-3">
        <div v-for="dayName in dayNames" :key="dayName" class="day-head">{{ dayName }}</div>
        <button
          v-for="cell in calendarCells"
          :key="cell.key"
          type="button"
          class="day-cell"
          :class="{
            'other-month': !cell.currentMonth,
            disabled: cell.disabled,
            selected: cell.date === selectedDate,
          }"
          :disabled="cell.disabled"
          @click="$emit('select-date', cell.date)"
        >
          {{ cell.day }}
        </button>
      </div>

      <div class="slots mt-3">
        <button
          v-for="slot in daySlots"
          :key="slot.time"
          type="button"
          class="slot-btn"
          :class="{ selected: selectedTime === slot.time }"
          :disabled="!slot.available"
          @click="$emit('select-time', slot.time)"
        >
          {{ slot.time }}
        </button>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-outline-secondary" @click="$emit('back')">Vissza</button>
      <button class="btn btn-dark" :disabled="!canProceedToReview" @click="$emit('next')">
        Tovább az ellenőrzéshez
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "DateTimeComponent",
  props: {
    selectedBarberMode: { type: String, required: true },
    earliestLoading: { type: Boolean, required: true },
    earliestOptions: { type: Array, required: true },
    selectedDate: { type: String, default: "" },
    selectedTime: { type: String, default: "" },
    selectedBarberId: { default: null },
    monthLabel: { type: String, required: true },
    dayNames: { type: Array, required: true },
    calendarCells: { type: Array, required: true },
    daySlots: { type: Array, required: true },
    canProceedToReview: { type: Boolean, required: true },
  },
  emits: [
    "load-earliest-options",
    "pick-earliest-option",
    "change-month",
    "select-date",
    "select-time",
    "back",
    "next",
  ],
};
</script>
