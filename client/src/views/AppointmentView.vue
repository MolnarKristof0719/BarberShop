<template>
  <section class="appointment-page">
    <div class="hero d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <p class="hero-kicker mb-1">Barber Shop</p>
        <h1 class="hero-title mb-1">Időpontfoglalás</h1>
        <p class="hero-subtitle mb-0">Válassz szolgáltatást, barbert, napot es időpontot.</p>
      </div>
      <div class="step-pill">Lépés: {{ currentStep }}/4</div>
    </div>

    <StepperComponent
      class="mt-3"
      :current-step="currentStep"
      :step-items="stepItems"
      @step-click="handleStepClick"
    />

    <div class="flow mt-4">
      <ServicesComponent
        v-if="currentStep === 1"
        :services="services"
        :selected-service-ids="selectedServiceIds"
        @toggle-service="toggleService"
        @next="goToStep(2)"
      />

      <BarbersComponent
        v-if="currentStep === 2"
        :selected-barber-mode="selectedBarberMode"
        :selected-barber-name="selectedBarberName"
        :active-barbers="activeBarbers"
        :selected-barber-id="selectedBarberId"
        :can-proceed-from-barber="canProceedFromBarber"
        @select-earliest-mode="selectEarliestMode"
        @select-barber="selectBarber"
        @open-reference="openReferenceModal"
        @back="goToStep(1)"
        @next="goToStep(3)"
      />

      <DateTimeComponent
        v-if="currentStep === 3"
        :selected-barber-mode="selectedBarberMode"
        :earliest-loading="earliestLoading"
        :earliest-options="earliestOptions"
        :selected-date="selectedDate"
        :selected-time="selectedTime"
        :selected-barber-id="selectedBarberId"
        :month-label="monthLabel"
        :day-names="dayNames"
        :calendar-cells="calendarCells"
        :day-slots="daySlots"
        :can-proceed-to-review="!!selectedDate && !!selectedTime && !!selectedBarberId"
        @load-earliest-options="loadEarliestOptions"
        @pick-earliest-option="pickEarliestOption"
        @change-month="changeMonth"
        @select-date="selectDate"
        @select-time="selectTime"
        @back="goToStep(2)"
        @next="goToStep(4)"
      />

      <ReviewComponent
        v-if="currentStep === 4"
        :selected-service-names="selectedServiceNames"
        :selected-barber-name="selectedBarberName"
        :selected-date="selectedDate"
        :selected-time="selectedTime"
        :submit-loading="submitLoading"
        :submit-error="submitError"
        @back="goToStep(3)"
        @submit="submitBooking"
      />
    </div>

    <div v-if="showReferenceModal" class="ref-modal-backdrop" @click="closeReferenceModal">
      <div class="ref-modal" @click.stop>
        <div class="d-flex align-items-center justify-content-between gap-2">
          <h3 class="mb-0">{{ referenceModalBarberName }} referenciakepek</h3>
          <button class="btn-close" type="button" @click="closeReferenceModal"></button>
        </div>
        <p class="mb-0 mt-3 text-muted">
          A referenciaképek tábla meg nincs bekötve, de a modal készen áll a későbbi listázásra.
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import appointmentService from "@/api/appointmentService";
import BarbersComponent from "@/components/Appointment/BarbersComponent.vue";
import DateTimeComponent from "@/components/Appointment/DateTimeComponent.vue";
import ReviewComponent from "@/components/Appointment/ReviewComponent.vue";
import ServicesComponent from "@/components/Appointment/ServicesComponent.vue";
import StepperComponent from "@/components/Appointment/StepperComponent.vue";
import { useAppointmentStore } from "@/stores/appointmentStore";
import { useBarberStore } from "@/stores/barberStore";
import { useServiceStore } from "@/stores/serviceStore";

export default {
  name: "AppointmentView",
  components: {
    StepperComponent,
    ServicesComponent,
    BarbersComponent,
    DateTimeComponent,
    ReviewComponent,
  },
  data() {
    return {
      currentStep: 1,
      selectedServiceIds: [],
      selectedBarberMode: "specific",
      selectedBarberId: null,
      selectedDate: "",
      selectedTime: "",
      monthCursor: this.formatMonth(new Date()),
      fullyBookedDates: [],
      daySlots: [],
      earliestOptions: [],
      earliestLoading: false,
      submitLoading: false,
      submitError: "",
      showReferenceModal: false,
      referenceModalBarberName: "",
      serviceStore: useServiceStore(),
      barberStore: useBarberStore(),
      appointmentStore: useAppointmentStore(),
    };
  },
  computed: {
    services() {
      return this.serviceStore.items || [];
    },
    barbers() {
      return this.barberStore.items || [];
    },
    activeBarbers() {
      return this.barbers
        .filter((barber) => barber && barber.id && barber.isActive)
        .sort((a, b) => this.barberName(a).localeCompare(this.barberName(b)));
    },
    selectedBarber() {
      return this.activeBarbers.find((barber) => barber.id === this.selectedBarberId) || null;
    },
    selectedBarberName() {
      return this.selectedBarber ? this.barberName(this.selectedBarber) : "";
    },
    canProceedFromBarber() {
      if (this.selectedBarberMode === "earliest") {
        return true;
      }
      return !!this.selectedBarberId;
    },
    selectedServiceNames() {
      return this.services
        .filter((service) => this.selectedServiceIds.includes(service.id))
        .map((service) => service.service);
    },
    monthDate() {
      const [year, month] = this.monthCursor.split("-").map(Number);
      return new Date(year, month - 1, 1);
    },
    monthLabel() {
      return this.monthDate.toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
      });
    },
    dayNames() {
      return ["H", "K", "Sze", "Cs", "P", "Szo", "V"];
    },
    stepItems() {
      return [
        { id: 1, label: "Szolgáltatás" },
        { id: 2, label: "Barber" },
        { id: 3, label: "Időpont" },
        { id: 4, label: "Ellenőrzés" },
      ];
    },
    calendarCells() {
      const firstDay = new Date(this.monthDate.getFullYear(), this.monthDate.getMonth(), 1);
      const startOffset = (firstDay.getDay() + 6) % 7;
      const startDate = new Date(firstDay);
      startDate.setDate(firstDay.getDate() - startOffset);

      const cells = [];
      const today = this.toDateString(new Date());
      for (let index = 0; index < 42; index += 1) {
        const current = new Date(startDate);
        current.setDate(startDate.getDate() + index);
        const dateString = this.toDateString(current);
        const currentMonth = current.getMonth() === this.monthDate.getMonth();
        const disabled =
          !currentMonth ||
          dateString < today ||
          this.fullyBookedDates.includes(dateString);

        cells.push({
          key: `${dateString}-${index}`,
          day: current.getDate(),
          date: dateString,
          currentMonth,
          disabled,
        });
      }
      return cells;
    },
  },
  methods: {
    formatMonth(date) {
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
    },
    toDateString(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    barberName(barber) {
      return barber?.user?.name || `Barber #${barber?.id || ""}`;
    },
    toggleService(id) {
      this.submitError = "";
      if (this.selectedServiceIds.includes(id)) {
        this.selectedServiceIds = this.selectedServiceIds.filter((item) => item !== id);
      } else {
        this.selectedServiceIds = [...this.selectedServiceIds, id];
      }
    },
    selectEarliestMode() {
      this.selectedBarberMode = "earliest";
      this.selectedBarberId = null;
      this.selectedDate = "";
      this.selectedTime = "";
      this.daySlots = [];
      this.earliestOptions = [];
    },
    async selectBarber(id) {
      this.selectedBarberMode = "specific";
      this.selectedBarberId = id;
      this.selectedDate = "";
      this.selectedTime = "";
      this.daySlots = [];
      this.earliestOptions = [];
      if (this.currentStep >= 3) {
        await this.fetchAvailability();
      }
    },
    async goToStep(step) {
      if (step === 2 && !this.selectedServiceIds.length) {
        return;
      }
      if (step === 3) {
        if (!this.canProceedFromBarber) {
          return;
        }
        if (this.selectedBarberMode === "specific") {
          await this.fetchAvailability();
        }
      }
      if (step === 4) {
        if (!this.selectedBarberId || !this.selectedDate || !this.selectedTime) {
          return;
        }
      }
      this.currentStep = step;
    },
    handleStepClick(step) {
      if (step < this.currentStep) {
        this.goToStep(step);
      }
    },
    openReferenceModal(barber) {
      this.referenceModalBarberName = this.barberName(barber);
      this.showReferenceModal = true;
    },
    closeReferenceModal() {
      this.showReferenceModal = false;
      this.referenceModalBarberName = "";
    },
    async changeMonth(delta) {
      const current = new Date(this.monthDate);
      current.setMonth(current.getMonth() + delta);
      this.monthCursor = this.formatMonth(current);
      this.selectedDate = "";
      this.selectedTime = "";
      this.daySlots = [];
      await this.fetchAvailability();
    },
    async fetchAvailability() {
      if (!this.selectedBarberId) {
        return;
      }

      const response = await appointmentService.getAvailability({
        barberId: this.selectedBarberId,
        month: this.monthCursor,
        date: this.selectedDate || undefined,
      });

      this.fullyBookedDates = response?.data?.fullyBookedDates || [];
      this.daySlots = response?.data?.slots || [];

      if (this.selectedDate && !this.daySlots.some((slot) => slot.available)) {
        this.selectedTime = "";
      }
    },
    async selectDate(date) {
      this.selectedDate = date;
      this.selectedTime = "";
      await this.fetchAvailability();
    },
    selectTime(time) {
      this.selectedTime = time;
      this.submitError = "";
    },
    async loadEarliestOptions() {
      this.earliestLoading = true;
      this.submitError = "";
      try {
        const response = await appointmentService.getEarliestOptions({
          limit: 10,
          daysAhead: 30,
          serviceIds: this.selectedServiceIds,
        });
        this.earliestOptions = response?.data || [];
      } finally {
        this.earliestLoading = false;
      }
    },
    pickEarliestOption(option) {
      this.selectedBarberId = option.barberId;
      this.selectedDate = option.appointmentDate;
      this.selectedTime = option.appointmentTime;
    },
    async submitBooking() {
      this.submitLoading = true;
      this.submitError = "";

      try {
        await this.appointmentStore.create({
          barberId: this.selectedBarberId,
          appointmentDate: this.selectedDate,
          appointmentTime: this.selectedTime,
          services: this.selectedServiceIds,
        });

        await this.$router.push({ name: "home" });
      } catch (error) {
        this.submitError =
          error?.response?.data?.message ||
          "A foglalás nem sikerült. Ellenőrizd az adatokat és próbáld újra.";
      } finally {
        this.submitLoading = false;
      }
    },
  },
  async mounted() {
    await Promise.all([this.serviceStore.getAll(), this.barberStore.getAll()]);
  },
};
</script>

<style scoped>
.appointment-page {
  min-height: 100%;
  padding: 10px;
}

.hero {
  border: 1px solid #e4e8ec;
  border-radius: 16px;
  padding: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
}

.hero-kicker {
  font-size: 12px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #c5a059;
  font-weight: 700;
}

.hero-title {
  font-size: clamp(1.45rem, 2.2vw, 2rem);
  color: #101820;
  font-weight: 700;
}

.hero-subtitle {
  color: #5f6b76;
}

.step-pill {
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid #d9e0e7;
  background: #fff;
  font-weight: 600;
}

:deep(.stepper) {
  display: grid;
  gap: 8px;
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

:deep(.stepper-item) {
  border: 1px solid #d9e0e7;
  border-radius: 12px;
  background: #fff;
  padding: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  text-align: left;
}

:deep(.stepper-item:disabled) {
  opacity: 0.6;
  cursor: not-allowed;
}

:deep(.stepper-item.done) {
  border-color: #89a0b7;
  background: #f4f8fc;
}

:deep(.stepper-item.active) {
  border-color: #111111;
  background: #f7f8fa;
}

:deep(.step-index) {
  width: 24px;
  height: 24px;
  border-radius: 999px;
  border: 1px solid #c9d2dc;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}

:deep(.step-label) {
  font-size: 0.9rem;
  font-weight: 600;
  color: #263746;
}

.flow {
  display: grid;
  gap: 16px;
}

:deep(.flow-card) {
  border: 1px solid #dfe4ea;
  border-radius: 14px;
  padding: 16px;
  background: #ffffff;
  opacity: 0.75;
}

:deep(.flow-card.active) {
  opacity: 1;
  border-color: #212529;
}

:deep(.flow-header) {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 12px;
}

:deep(.flow-header h2) {
  font-size: 1.05rem;
  margin: 0;
}

:deep(.card-list) {
  display: grid;
  gap: 10px;
}

:deep(.select-card),
:deep(.barber-card) {
  width: 100%;
  text-align: left;
  border: 1px solid #d8dee5;
  border-radius: 12px;
  padding: 12px 14px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

:deep(.select-card.selected),
:deep(.barber-card.selected) {
  border-color: #111111;
  background: #f7f8fa;
}

:deep(.barber-main) {
  display: flex;
  align-items: center;
  gap: 10px;
}

:deep(.avatar) {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #dce3ea;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  font-weight: 700;
  color: #25313d;
}

:deep(.avatar img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

:deep(.barber-name) {
  font-weight: 700;
}

:deep(.barber-intro) {
  color: #6b737c;
  font-size: 0.9rem;
}

:deep(.calendar-tools) {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

:deep(.calendar-grid) {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 6px;
}

:deep(.day-head) {
  text-align: center;
  font-size: 0.78rem;
  color: #6c757d;
  font-weight: 700;
}

:deep(.day-cell) {
  border: 1px solid #dce3ea;
  border-radius: 8px;
  background: #fff;
  min-height: 38px;
}

:deep(.day-cell.selected) {
  background: #111111;
  color: #fff;
  border-color: #111111;
}

:deep(.day-cell.other-month),
:deep(.day-cell.disabled) {
  color: #b8bec5;
  background: #f1f3f5;
  border-color: #e5e8eb;
}

:deep(.slots) {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

:deep(.slot-btn) {
  border: 1px solid #d4dbe2;
  background: #fff;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: 0.9rem;
}

:deep(.slot-btn.selected) {
  background: #111111;
  color: #fff;
  border-color: #111111;
}

:deep(.actions) {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

:deep(.summary-list) {
  margin: 0;
  padding-left: 18px;
}

:deep(.empty-state) {
  color: #6c757d;
  margin: 0;
}

.ref-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.ref-modal {
  width: min(560px, 100%);
  border-radius: 14px;
  background: #fff;
  border: 1px solid #d8dee5;
  padding: 16px;
}

@media (max-width: 767px) {
  :deep(.stepper) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  :deep(.actions) {
    justify-content: stretch;
  }

  :deep(.actions .btn) {
    flex: 1;
  }
}
</style>
