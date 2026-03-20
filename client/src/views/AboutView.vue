<template>
  <section class="about-page">
    <section class="home-barbers mt-4">
      <p class="section-title mb-5">MEET THE BARBERS</p>

      <div class="carousel-shell" v-if="activeBarber">
        <button class="nav-arrow nav-left" type="button" @click="prevBarber" aria-label="Előző barber">
          <i class="bi bi-chevron-left"></i>
        </button>

        <article class="barber-focus-card">
          <div class="focus-image-wrap">
            <img v-if="activeBarber.profilePicture" :src="profileImage(activeBarber.profilePicture)"
              :alt="activeBarber.user?.name || 'Barber kép'" class="focus-image" />
            <div v-else class="focus-image fallback d-flex align-items-center justify-content-center">
              <i class="bi bi-person-circle fs-1"></i>
            </div>
          </div>

          <div class="focus-content">
            <h2 class="focus-name mb-2">{{ activeBarber.user?.name || `Barber #${activeBarber.id}` }}</h2>
            <p class="focus-role mb-4">Barber</p>
            <div class="focus-rating" v-if="activeBarber">
              <div class="rating-stars">
                <i v-for="n in 5" :key="n" class="bi" :class="n <= roundedRating ? 'bi-star-fill' : 'bi-star'"></i>
              </div>
              <p class="rating-text mb-0">
                {{ ratingText }}
              </p>
            </div>
            <p class="focus-intro mb-0">
              {{ activeBarber.introduction || "Nincs bemutatkozás ehhez a barberhez." }}
            </p>
          </div>
        </article>

        <button class="nav-arrow nav-right" type="button" @click="nextBarber" aria-label="Következő barber">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>

      <div class="empty-state" v-else-if="!loading">
        Jelenleg nincs megjeleníthető barber.
      </div>
      <div class="loading-state" v-else>
        <i class="bi bi-hourglass-split me-2"></i> Betöltés...
      </div>

      <div class="dot-nav mt-4" v-if="displayItems.length > 1">
        <button v-for="(item, index) in displayItems" :key="item.id" class="dot-btn"
          :class="{ active: index === activeIndex }" type="button" @click="activeIndex = index"
          :aria-label="`Barber ${index + 1}`"></button>
      </div>
      <RouterLink to="/barber" class="btn btn-outline-dark all-barbers">
        Összes barber megtekintése
      </RouterLink>
    </section>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useBarberStore } from "@/stores/barberStore";
import { useReviewStore } from "@/stores/reviewStore";
import { resolveMediaUrl } from "@/utils/media";

export default {
  name: "AboutView",
  data() {
    return {
      activeIndex: 0,
      reviewStats: {},
      reviewStore: useReviewStore(),
    };
  },
  computed: {
    ...mapState(useBarberStore, ["items", "loading"]),
    displayItems() {
      if (!Array.isArray(this.items)) return [];
      return this.items.filter((item) => item && item.id);
    },
    activeBarber() {
      if (!this.displayItems.length) return null;
      const safeIndex = Math.min(this.activeIndex, this.displayItems.length - 1);
      return this.displayItems[safeIndex];
    },
    activeRating() {
      if (!this.activeBarber?.id) return { avg: 0, count: 0 };
      return this.reviewStats[this.activeBarber.id] || { avg: 0, count: 0 };
    },
    roundedRating() {
      return Math.round(this.activeRating.avg || 0);
    },
    ratingText() {
      const avg = this.activeRating.avg || 0;
      const count = this.activeRating.count || 0;
      if (!count) return "Még nincs értékelés";
      return `${avg.toFixed(1)} / 5 (${count} vélemény)`;
    },
  },
  methods: {
    ...mapActions(useBarberStore, ["getAll"]),
    buildReviewStats(reviews) {
      const stats = {};
      (reviews || []).forEach((review) => {
        const barberId = Number(review?.barberId);
        const rating = Number(review?.rating || 0);
        if (!barberId || !rating) return;
        if (!stats[barberId]) {
          stats[barberId] = { sum: 0, count: 0, avg: 0 };
        }
        stats[barberId].sum += rating;
        stats[barberId].count += 1;
        stats[barberId].avg = stats[barberId].sum / stats[barberId].count;
      });
      return stats;
    },
    async loadReviews() {
      try {
        const reviews = await this.reviewStore.getAll();
        this.reviewStats = this.buildReviewStats(reviews);
      } catch {
        this.reviewStats = {};
      }
    },
    nextBarber() {
      if (!this.displayItems.length) return;
      this.activeIndex = (this.activeIndex + 1) % this.displayItems.length;
    },
    prevBarber() {
      if (!this.displayItems.length) return;
      this.activeIndex = (this.activeIndex - 1 + this.displayItems.length) % this.displayItems.length;
    },
    profileImage(path) {
      return resolveMediaUrl(path);
    },
  },
  async mounted() {
    await Promise.all([this.getAll(), this.loadReviews()]);
  },
};
</script>

<style scoped>
.all-barbers {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 30px auto 0;
  padding: 10px;
  border-radius: 30px;
  max-width: 300px;
}

.about-page {
  min-height: 100%;
}

.home-barbers {
  background: #d8d8d8;
  border-radius: 14px;
  padding: 42px 18px 30px;
}

.section-title {
  text-align: center;
  color: #1b1b1b;
  letter-spacing: 0.06em;
  font-size: clamp(1.4rem, 2.2vw, 2.8rem);
  font-family: Georgia, "Times New Roman", serif;
}

.carousel-shell {
  display: grid;
  grid-template-columns: 24px minmax(130px, 320px) 24px;
  align-items: center;
  gap: 16px;
  max-width: 400px;
  margin: 0 auto;
  min-height: 800px;
}

.barber-focus-card {
  background: #ececec;
  border: 1px solid #d0d0d0;
}

.focus-image-wrap {
  width: 100%;
  height: clamp(200px, 34vw, 380px);
  background: #111111;
}

.focus-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.fallback {
  color: #c7c7c7;
  background: #1d1d1d;
}

.focus-content {
  background: #f6f6f6;
  padding: 24px 20px 30px;
  text-align: center;
}

.focus-name {
  color: #c5a059;
  font-size: 1.35rem;
  font-family: Georgia, "Times New Roman", serif;
}

.focus-role {
  color: #5f5f5f;
  font-style: italic;
}

.focus-rating {
  display: grid;
  gap: 6px;
  justify-items: center;
  margin-bottom: 16px;
}

.rating-stars {
  color: #c5a059;
  font-size: 1.05rem;
  display: flex;
  gap: 6px;
}

.rating-text {
  color: #4f4f4f;
  font-weight: 600;
}

.focus-intro {
  color: #222;
  line-height: 1.7;
  max-width: 70ch;
  margin: 0 auto;
}

.nav-arrow {
  width: 48px;
  height: 48px;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: #1d1d1d;
  font-size: 1.8rem;
}

.nav-left {
  justify-self: end;
}

.nav-right {
  justify-self: start;
}

.nav-arrow:hover {
  background: rgba(17, 17, 17, 0.08);
}

.dot-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.dot-btn {
  width: 10px;
  height: 10px;
  border: 0;
  border-radius: 50%;
  background: #8f8f8f;
}

.dot-btn.active {
  background: #1a1a1a;
}

.empty-state,
.loading-state {
  text-align: center;
  color: #4b4b4b;
}

@media (max-width: 991.98px) {
  .carousel-shell {
    grid-template-columns: 1fr;
  }

  .nav-left {
    order: 2;
    justify-self: start;
  }

  .nav-right {
    order: 3;
    justify-self: end;
    margin-top: -48px;
  }

  .barber-focus-card {
    order: 1;
  }
}
</style>
