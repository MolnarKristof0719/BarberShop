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
        :disabled-service-ids="disabledServiceIds"
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
        :total-price-text="selectedServiceTotalText"
        :submit-loading="submitLoading"
        :submit-error="submitError"
        @back="goToStep(3)"
        @submit="submitBooking"
      />
    </div>

    <div v-if="showReferenceModal" class="ref-modal-backdrop" @click="closeReferenceModal">
      <div class="ref-modal" @click.stop>
        <div class="d-flex align-items-center justify-content-between gap-2">
          <h3 class="mb-0">{{ referenceModalBarberName }} referenciaképek</h3>
          <button class="btn-close" type="button" @click="closeReferenceModal"></button>
        </div>
        <div class="ref-modal-body mt-3">
          <p v-if="referenceModalLoading" class="mb-0 text-muted">
            Referenciaképek betöltése...
          </p>
          <p v-else-if="referenceModalError" class="mb-0 text-danger">
            {{ referenceModalError }}
          </p>
          <p v-else-if="!referenceModalPictures.length" class="mb-0 text-muted">
            Ehhez a barberhez még nincs feltöltött referenciakép.
          </p>
          <div v-else class="ref-grid">
            <a
              v-for="picture in referenceModalPictures"
              :key="picture.id"
              :href="resolveImage(picture.picture)"
              target="_blank"
              rel="noopener noreferrer"
              class="ref-item"
            >
              <img :src="resolveImage(picture.picture)" alt="Barber referencia kep" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import appointmentService from "@/api/appointmentService";
import barberService from "@/api/barberService";
import BarbersComponent from "@/components/Appointment/BarbersComponent.vue";
import DateTimeComponent from "@/components/Appointment/DateTimeComponent.vue";
import ReviewComponent from "@/components/Appointment/ReviewComponent.vue";
import ServicesComponent from "@/components/Appointment/ServicesComponent.vue";
import StepperComponent from "@/components/Appointment/StepperComponent.vue";
import { useAppointmentStore } from "@/stores/appointmentStore";
import { useBarberStore } from "@/stores/barberStore";
import { useServiceStore } from "@/stores/serviceStore";
import { useUsersmeAppointmentStore } from "@/stores/usersmeappointmentStore";
import { resolveMediaUrl } from "@/utils/media";

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
      referenceModalPictures: [],
      referenceModalLoading: false,
      referenceModalError: "",
      serviceStore: useServiceStore(),
      barberStore: useBarberStore(),
      appointmentStore: useAppointmentStore(),
      usersmeAppointmentStore: useUsersmeAppointmentStore(),
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
        .filter((barber) => barber && barber.id)
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
      return !!this.selectedBarberId && !!this.selectedBarber?.isActive;
    },
    selectedServiceNames() {
      return this.services
        .filter((service) => this.selectedServiceIds.includes(service.id))
        .map((service) => service.service);
    },
    selectedServiceTotal() {
      return this.services
        .filter((service) => this.selectedServiceIds.includes(service.id))
        .reduce((sum, service) => {
          const raw = service?.price ?? "";
          const numeric = Number(String(raw).replace(/[^\d]/g, ""));
          return sum + (Number.isFinite(numeric) ? numeric : 0);
        }, 0);
    },
    selectedServiceTotalText() {
      return Number.isFinite(this.selectedServiceTotal)
        ? this.selectedServiceTotal.toLocaleString("hu-HU")
        : "0";
    },
    serviceTypeById() {
      return this.services.reduce((acc, service) => {
        acc[service.id] = this.getServiceType(service.service);
        return acc;
      }, {});
    },
    disabledServiceIds() {
      const selectedSet = new Set(this.selectedServiceIds);
      const selectedTypes = new Set(
        this.selectedServiceIds
          .map((id) => this.serviceTypeById[id])
          .filter(Boolean),
      );

      const conflictByType = {
        one_length: ["fade", "hair_beard_combo"],
        fade: ["one_length", "hair_beard_combo"],
        beard_trim: ["hair_beard_combo"],
        hair_beard_combo: ["one_length", "fade", "beard_trim"],
      };

      return this.services
        .filter((service) => !selectedSet.has(service.id))
        .filter((service) => {
          const type = this.serviceTypeById[service.id];
          if (!type) {
            return false;
          }
          return [...selectedTypes].some((selectedType) =>
            (conflictByType[selectedType] || []).includes(type),
          );
        })
        .map((service) => service.id);
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
    normalizeText(value) {
      return String(value || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
    },
    getServiceType(name) {
      const normalized = this.normalizeText(name);
      const isHaircut = normalized.includes("hajvag");
      const isFade = normalized.includes("atmenetes") && isHaircut;
      const isOneLength = normalized.includes("egyhosszu") && isHaircut;
      const isCombo = isHaircut && normalized.includes("szakall");
      const isBeardTrim =
        normalized.includes("szakall") && normalized.includes("igazit");

      if (isCombo) return "hair_beard_combo";
      if (isFade) return "fade";
      if (isOneLength) return "one_length";
      if (isBeardTrim) return "beard_trim";
      return null;
    },
    barberName(barber) {
      return barber?.user?.name || `Barber #${barber?.id || ""}`;
    },
    resolveImage(path) {
      return resolveMediaUrl(path);
    },
    toggleService(id) {
      this.submitError = "";
      if (
        this.disabledServiceIds.includes(id) &&
        !this.selectedServiceIds.includes(id)
      ) {
        return;
      }
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
    async openReferenceModal(barber) {
      this.referenceModalBarberName = this.barberName(barber);
      this.showReferenceModal = true;
      document.body.classList.add("modal-open-lock");
      this.referenceModalLoading = true;
      this.referenceModalError = "";
      this.referenceModalPictures = [];

      try {
        if (!barber?.id) {
          throw new Error("Missing barber id");
        }
        const response = await barberService.getById(barber.id);
        const pictures = response?.data?.reference_pictures;
        this.referenceModalPictures = Array.isArray(pictures) ? pictures : [];
      } catch {
        this.referenceModalError = "A referenciaképek betöltése nem sikerült.";
      } finally {
        this.referenceModalLoading = false;
      }
    },
    closeReferenceModal() {
      this.showReferenceModal = false;
      this.referenceModalBarberName = "";
      this.referenceModalPictures = [];
      this.referenceModalLoading = false;
      this.referenceModalError = "";
      document.body.classList.remove("modal-open-lock");
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

        await this.usersmeAppointmentStore.getAll();
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
    document.body.classList.add("appointment-wide");
    await Promise.all([this.serviceStore.getAll(), this.barberStore.getAll()]);
  },
  beforeUnmount() {
    document.body.classList.remove("appointment-wide");
    document.body.classList.remove("modal-open-lock");
  },
};
</script>

<style scoped>
:global(body.appointment-wide .my-container) {
  max-width: 1800px;
}

:global(body.modal-open-lock) {
  overflow: hidden;
}

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
  grid-template-columns: 1fr minmax(0, 2fr) minmax(0, 2fr) 1fr;
  gap: 16px;
  width: 100%;
  max-width: 1800px;
  margin: 0 auto;
}

.flow > * {
  grid-column: 2 / 4;
  width: 100%;
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

:deep(.select-card.disabled-service) {
  background: #f1f3f5;
  color: #9aa3ad;
  border-color: #e2e7ec;
  cursor: not-allowed;
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

:deep(.service-info) {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

:deep(.service-name) {
  font-weight: 600;
}

:deep(.service-price) {
  color: #8f2438;
  font-weight: 700;
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
  max-height: min(84vh, 760px);
  border-radius: 14px;
  background: #fff;
  border: 1px solid #d8dee5;
  padding: 16px;
  display: grid;
  grid-template-rows: auto 1fr;
}

.ref-modal-body {
  overflow-y: auto;
  overscroll-behavior: contain;
  padding-right: 4px;
}

.ref-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.ref-item {
  display: block;
  border: 1px solid #dce3ea;
  border-radius: 10px;
  overflow: hidden;
  background: #f8fafc;
}

.ref-item img {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
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

  .ref-grid {
    grid-template-columns: 1fr;
  }
}
</style>
