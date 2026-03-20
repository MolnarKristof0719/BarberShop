<template>
  <section class="review-page">
    <div class="hero">
      <p class="hero-kicker mb-1">Fiók</p>
      <h1 class="hero-title mb-1">Vélemény írása</h1>
      <p class="hero-subtitle mb-0">
        Oszd meg az élményedet az adott barbertől kapott szolgáltatásról.
      </p>
    </div>

    <div v-if="errorMsg" class="state-box mt-3 error-box">
      {{ errorMsg }}
    </div>

    <div v-else class="review-card mt-3">
      <div class="appointment-meta">
        <div class="meta-item">
          <span class="meta-label">Időpont</span>
          <span class="meta-value">{{ appointmentDate }} {{ appointmentTime }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Barber</span>
          <span class="meta-value">{{ barberName }}</span>
        </div>
      </div>

      <div class="rating-block">
        <p class="block-title">Hány csillagot adsz?</p>
        <div class="stars">
          <button
            v-for="n in 5"
            :key="n"
            type="button"
            class="star-btn"
            :class="{ active: n <= rating }"
            @click="setRating(n)"
            :aria-label="`${n} csillag`"
          >
            <i class="bi" :class="n <= rating ? 'bi-star-fill' : 'bi-star'"></i>
          </button>
        </div>
        <p class="rating-text">{{ rating ? `${rating}/5` : "Válassz csillagot" }}</p>
      </div>

      <div class="comment-block">
        <label class="block-title" for="review-comment">Rövid vélemény (nem kötelező)</label>
        <textarea
          id="review-comment"
          v-model="comment"
          class="form-control"
          rows="4"
          placeholder="Ide írhatod a véleményedet..."
        ></textarea>
      </div>

      <div class="actions">
        <button class="btn btn-outline-secondary" type="button" @click="goBack" :disabled="saving">
          Vissza
        </button>
        <button class="btn btn-dark" type="button" @click="saveReview" :disabled="!canSubmit">
          {{ saving ? "Mentés..." : "Vélemény mentése" }}
        </button>
      </div>
    </div>
  </section>
</template>

<script>
import { mapActions } from "pinia";
import { useUsersmeAppointmentStore } from "@/stores/usersmeappointmentStore";
import { useReviewStore } from "@/stores/reviewStore";
import { useToastStore } from "@/stores/toastStore";

export default {
  name: "ReviewView",
  data() {
    return {
      appointment: null,
      rating: 0,
      comment: "",
      saving: false,
      errorMsg: "",
      toast: useToastStore(),
    };
  },
  computed: {
    appointmentId() {
      const raw = this.$route?.query?.appointmentId;
      const parsed = Number(raw);
      return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
    },
    appointmentDate() {
      if (!this.appointment?.appointmentDate) return "-";
      return new Date(this.appointment.appointmentDate).toLocaleDateString("hu-HU");
    },
    appointmentTime() {
      const value = this.appointment?.appointmentTime;
      if (!value) return "-";
      const raw = String(value);
      return raw.length >= 5 ? raw.slice(0, 5) : raw;
    },
    barberName() {
      return (
        this.appointment?.barber?.user?.name ||
        this.appointment?.barber?.name ||
        (this.appointment?.barberId ? `Barber #${this.appointment.barberId}` : "-")
      );
    },
    canSubmit() {
      return this.rating > 0 && !this.saving;
    },
  },
  methods: {
    ...mapActions(useUsersmeAppointmentStore, ["getById"]),
    ...mapActions(useReviewStore, ["create"]),
    setRating(value) {
      this.rating = value;
    },
    goBack() {
      this.$router.push({ name: "usersmeappointment" });
    },
    async loadAppointment() {
      if (!this.appointmentId) {
        this.errorMsg = "Hiányzik az időpont azonosítója.";
        return;
      }
      try {
        this.appointment = await this.getById(this.appointmentId);
      } catch (err) {
        this.errorMsg = "Nem sikerült betölteni az időpont adatait.";
      }
    },
    async saveReview() {
      if (!this.canSubmit || !this.appointmentId) return;
      this.saving = true;
      this.errorMsg = "";
      try {
        const payload = {
          rating: this.rating,
          comment: this.comment.trim() ? this.comment.trim() : "",
        };
        await this.create(this.appointmentId, payload);
        this.toast.messages.push("Köszönjük a véleményt!");
        this.toast.show("Success");
        this.$router.push({ name: "usersmeappointment" });
      } catch (err) {
        this.errorMsg = "Nem sikerült elmenteni a véleményt.";
      } finally {
        this.saving = false;
      }
    },
  },
  async mounted() {
    await this.loadAppointment();
  },
};
</script>

<style scoped>
.review-page {
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

.error-box {
  border-color: #f2c7c7;
  background: #fff5f5;
  color: #9b1c1c;
}

.review-card {
  border: 1px solid #e4e8ec;
  border-radius: 16px;
  background: #ffffff;
  padding: 20px;
  box-shadow: 0 10px 24px rgba(17, 17, 17, 0.06);
}

.appointment-meta {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  margin-bottom: 16px;
}

.meta-item {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 10px 12px;
}

.meta-label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6c757d;
  font-weight: 700;
  margin-bottom: 4px;
}

.meta-value {
  font-weight: 600;
  color: #111111;
}

.rating-block {
  margin: 18px 0;
}

.block-title {
  font-weight: 700;
  color: #111111;
  margin-bottom: 8px;
}

.stars {
  display: flex;
  gap: 8px;
}

.star-btn {
  border: 1px solid #e3e6ea;
  background: #ffffff;
  border-radius: 10px;
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  font-size: 1.1rem;
  color: #c5a059;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.star-btn.active {
  background: #111111;
  color: #f8d28f;
  border-color: #111111;
}

.star-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(17, 17, 17, 0.1);
}

.rating-text {
  margin-top: 6px;
  color: #5f6b76;
}

.comment-block {
  margin-top: 12px;
}

.actions {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-top: 18px;
}
</style>
