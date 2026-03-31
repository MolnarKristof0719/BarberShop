<template>
  <section class="usersme-appointments-page">
    <div class="hero">
      <p class="hero-kicker mb-1">Fiók</p>
      <h1 class="hero-title mb-1">Hozzám tartozó foglalások</h1>
      <p class="hero-subtitle mb-0">Itt láthatod azokat a foglalásokat, ahol te vagy a barber.</p>
    </div>

    <div v-if="loading" class="state-box mt-3">Betöltés...</div>
    <div v-else-if="!pendingItems.length" class="state-box mt-3">Még nincs hozzád tartozó függőben lévő foglalás.</div>

    <template v-else>
      <UsersmeAppointmentsCards
        v-if="pendingItems.length"
        class="mt-3"
        :appointments="pendingItems"
        :loading="loading"
        context="barber"
        :showReview="false"
        @cancel="cancelAppointment"
      />
    </template>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import UsersmeAppointmentsCards from "@/components/Appointment/UsersmeAppointmentsCards.vue";
import { useAppointmentStore } from "@/stores/appointmentStore";

export default {
  name: "UsersmeAppointmentBarberView",
  components: {
    UsersmeAppointmentsCards,
  },
  computed: {
    ...mapState(useAppointmentStore, ["items", "loading"]),
    sortedItems() {
      return [...this.items].sort((a, b) => {
        const keyA = `${a?.appointmentDate || ""} ${String(a?.appointmentTime || "").slice(0, 8)}`;
        const keyB = `${b?.appointmentDate || ""} ${String(b?.appointmentTime || "").slice(0, 8)}`;
        return keyA.localeCompare(keyB);
      });
    },
    pendingItems() {
      return this.sortedItems.filter((item) => item?.status === "booked");
    },
  },
  methods: {
    ...mapActions(useAppointmentStore, ["getAll", "cancelForBarber"]),
    async refreshAppointments() {
      await this.getAll();
    },
    async cancelAppointment(id) {
      await this.cancelForBarber(id);
    },
  },
  async mounted() {
    await this.refreshAppointments();
  },
  async activated() {
    await this.refreshAppointments();
  },
};
</script>

<style scoped>
.usersme-appointments-page {
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

.state-box {
  border: 1px solid #e4e8ec;
  border-radius: 12px;
  background: #ffffff;
  padding: 14px;
  color: #5f6b76;
}

</style>
