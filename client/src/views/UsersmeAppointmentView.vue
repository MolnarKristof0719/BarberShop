<template>
  <section class="usersme-appointments-page">
    <div class="hero">
      <p class="hero-kicker mb-1">Fiók</p>
      <h1 class="hero-title mb-1">Foglalásaim</h1>
      <p class="hero-subtitle mb-0">Itt láthatod a saját időpontfoglalásaidat.</p>
    </div>

    <div v-if="loading" class="state-box mt-3">Betöltés...</div>
    <div v-else-if="!items.length" class="state-box mt-3">Még nincs foglalásod.</div>

    <template v-else>
      <UsersmeAppointmentsCards
        v-if="activeItems.length"
        class="mt-3"
        :appointments="activeItems"
        :loading="loading"
        @cancel="cancelAppointment"
      />

      <div v-if="cancelledItems.length" class="cancelled-block">
        <hr class="section-separator" />
        <p class="cancelled-title mb-2">Lemondott foglalások</p>
        <UsersmeAppointmentsCards
          :appointments="cancelledItems"
          :loading="loading"
          @cancel="cancelAppointment"
        />
      </div>
    </template>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import UsersmeAppointmentsCards from "@/components/Appointment/UsersmeAppointmentsCards.vue";
import { useUsersmeAppointmentStore } from "@/stores/usersmeappointmentStore";

export default {
  name: "UsersmeAppointmentView",
  components: {
    UsersmeAppointmentsCards,
  },
  computed: {
    ...mapState(useUsersmeAppointmentStore, ["items", "loading"]),
    activeItems() {
      return this.items.filter((item) => item?.status !== "cancelled");
    },
    cancelledItems() {
      return this.items.filter((item) => item?.status === "cancelled");
    },
  },
  methods: {
    ...mapActions(useUsersmeAppointmentStore, ["getAll", "cancel"]),
    async cancelAppointment(id) {
      await this.cancel(id);
    },
  },
  async mounted() {
    await this.getAll();
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

.cancelled-block {
  margin-top: 18px;
}

.section-separator {
  border: 0;
  border-top: 1px solid #d9e0e7;
  margin: 0 0 12px 0;
}

.cancelled-title {
  color: #6b7280;
  font-weight: 700;
  letter-spacing: 0.03em;
}
</style>
