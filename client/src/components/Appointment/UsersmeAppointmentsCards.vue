<template>
  <section class="appointments-list">
    <article v-for="appointment in appointments" :key="appointment.id" class="booking-card">
      <header class="booking-header">
        <h2 class="booking-title mb-0">BARBER <span class="accent">SHOP</span></h2>
      </header>

      <div class="booking-content">
        <div class="details-grid">
          <div class="detail-item">
            <div class="detail-label">Időpont</div>
            <div class="detail-value">
              {{ formatDate(appointment.appointmentDate) }}
              <span class="accent">|</span>
              {{ formatTime(appointment.appointmentTime) }}
            </div>
          </div>

          <div class="detail-item">
            <div class="detail-label">{{ personLabel }}</div>
            <div class="detail-value">{{ personName(appointment) }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">Állapot</div>
            <div class="detail-value">
              <span class="status-chip" :class="statusClass(appointment.status)">
                {{ statusLabel(appointment.status) }}
              </span>
            </div>
          </div>

          <div class="detail-item mb-0">
            <div class="detail-label">Szolgáltatások</div>
            <div>
              <span v-for="service in appointment.services || []" :key="service.id" class="service-tag">
                {{ service.service }}
              </span>
              <span v-if="!(appointment.services || []).length" class="service-tag">
                Nincs szolgáltatás
              </span>
            </div>
          </div>
        </div>
        <div class="actions">
          <div class="review">
            <button class="btn btn-outline-dark btn-sm" type="button"
              v-if="showReview && appointment.status === 'completed'"
              :disabled="isReviewed(appointment.id)"
              @click="$emit('review', appointment.id)">
              {{ isReviewed(appointment.id) ? "Vélemény írva" : "Vélemény" }}
            </button>
          </div>
          <div class="delete">
            <button class="btn btn-outline-danger btn-sm" type="button"
              :disabled="appointment.status === 'cancelled' || loading || appointment.status === 'completed'"
              @click="$emit('cancel', appointment.id)">
              Lemondás
            </button>
          </div>
        </div>
      </div>
    </article>
  </section>
</template>

<script>
export default {
  name: "UsersmeAppointmentsCards",
  props: {
    appointments: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    reviewedIds: { type: Object, default: () => new Set() },
    context: { type: String, default: "customer" },
    showReview: { type: Boolean, default: true },
  },
  emits: ["cancel", "review"],
  computed: {
    personLabel() {
      return this.context === "barber" ? "Vendég" : "Barber";
    },
  },
  methods: {
    formatDate(value) {
      if (!value) return "-";
      return new Date(value).toLocaleDateString("hu-HU");
    },
    formatTime(value) {
      if (!value) return "-";
      const raw = String(value);
      return raw.length >= 5 ? raw.slice(0, 5) : raw;
    },
    barberName(appointment) {
      return (
        appointment?.barber?.user?.name ||
        appointment?.barber?.name ||
        `Barber #${appointment?.barberId || ""}`
      );
    },
    customerName(appointment) {
      return (
        appointment?.user?.name ||
        (appointment?.userId ? `Vendég #${appointment.userId}` : "-")
      );
    },
    personName(appointment) {
      return this.context === "barber"
        ? this.customerName(appointment)
        : this.barberName(appointment);
    },
    statusLabel(status) {
      const map = {
        booked: "Függőben",
        completed: "Teljesítve",
        cancelled: "Lemondva",
      };
      return map[status] || status || "-";
    },
    statusClass(status) {
      if (status === "completed") return "done";
      if (status === "cancelled") return "cancelled";
      return "pending";
    },
    isReviewed(appointmentId) {
      return this.reviewedIds instanceof Set && this.reviewedIds.has(appointmentId);
    },
  },
};
</script>

<style scoped>
.appointments-list {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.booking-card {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e1e5ea;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.06);
}

.booking-header {
  background: #111111;
  color: #ffffff;
  padding: 14px 14px;
}

.booking-title {
  font-size: 0.95rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.accent {
  color: #c5a059;
}

.booking-content {
  padding: 12px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.details-grid {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 10px;
  flex: 1;
}

.detail-item {
  margin-bottom: 10px;
}

.detail-label {
  font-size: 0.76rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  margin-bottom: 3px;
}

.detail-value {
  font-size: 0.92rem;
  color: #111111;
  font-weight: 600;
}

.service-tag {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.76rem;
  margin: 3px 6px 0 0;
}

.status-chip {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.status-chip.pending {
  background: #fff4d6;
  color: #8a6200;
}

.status-chip.done {
  background: #e6f6ea;
  color: #126437;
}

.status-chip.cancelled {
  background: #fde8eb;
  color: #8f1d2d;
}

.delete {
  margin-top: 10px;
  display: flex;
  justify-content: flex-end;
}

.review {
  margin-top: 10px;
  display: flex;
  justify-content: flex-start;
}

.actions {
  margin-top: auto;
  padding-top: 10px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}
</style>
