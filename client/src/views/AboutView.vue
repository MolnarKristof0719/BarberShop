<template>
  <section class="about-page">
    <section class="home-barbers mt-4">
      <p class="section-title mb-5">MEET THE BARBERS</p>

      <div class="carousel-shell" v-if="displayItems.length">
        <button class="nav-arrow nav-left" type="button" @click="goPrevBarber" aria-label="Előző barber">
          <i class="bi bi-chevron-left"></i>
        </button>

        <transition name="barber-fade-slide" mode="out-in">
          <div class="focus-grid" :key="`barber-slide-${activeIndex}`">
            <article
              v-for="barber in visibleBarbers"
              :key="barber.id"
              class="barber-focus-card"
              :class="{ 'is-active': barber.id === activeBarber?.id }"
              @click="setActiveById(barber.id)"
            >
              <div class="focus-image-wrap">
                <img v-if="barber.profilePicture" :src="profileImage(barber.profilePicture)"
                  :alt="barber.user?.name || 'Barber kép'" class="focus-image" />
                <div v-else class="focus-image fallback d-flex align-items-center justify-content-center">
                  <i class="bi bi-person-circle fs-1"></i>
                </div>
              </div>

              <div class="focus-content">
                <h2 class="focus-name mb-2">{{ barber.user?.name || `Barber #${barber.id}` }}</h2>
                <p class="focus-role mb-4">Barber</p>
                <div class="focus-rating">
                  <div class="rating-stars">
                    <i
                      v-for="n in 5"
                      :key="n"
                      class="bi"
                      :class="n <= getRoundedRating(barber.id) ? 'bi-star-fill' : 'bi-star'"
                    ></i>
                  </div>
                  <p class="rating-text mb-0">
                    {{ getRatingText(barber.id) }}
                  </p>
                </div>
                <p class="focus-intro mb-0">
                  {{ barber.introduction || "Nincs bemutatkozás ehhez a barberhez." }}
                </p>
              </div>
            </article>
          </div>
        </transition>

        <button class="nav-arrow nav-right" type="button" @click="goNextBarber" aria-label="Következő barber">
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
          :class="{ active: index === activeIndex }" type="button" @click="setActiveIndex(index)"
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
      autoSlideTimer: null,
    };
  },
  watch: {
    displayItems(newItems) {
      if (this.activeIndex >= newItems.length) {
        this.activeIndex = 0;
      }
      this.restartAutoSlide();
    },
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
    visibleBarbers() {
      const count = this.displayItems.length;
      if (!count) return [];
      if (count <= 3) return this.displayItems;

      const prev = (this.activeIndex - 1 + count) % count;
      const next = (this.activeIndex + 1) % count;
      return [this.displayItems[prev], this.displayItems[this.activeIndex], this.displayItems[next]];
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
    getBarberRatingData(barberId) {
      return this.reviewStats[barberId] || { avg: 0, count: 0 };
    },
    getRoundedRating(barberId) {
      return Math.round(this.getBarberRatingData(barberId).avg || 0);
    },
    getRatingText(barberId) {
      const rating = this.getBarberRatingData(barberId);
      if (!rating.count) return "Még nincs értékelés";
      return `${rating.avg.toFixed(1)} / 5 (${rating.count} vélemény)`;
    },
    nextBarber() {
      if (!this.displayItems.length) return;
      this.activeIndex = (this.activeIndex + 1) % this.displayItems.length;
    },
    prevBarber() {
      if (!this.displayItems.length) return;
      this.activeIndex = (this.activeIndex - 1 + this.displayItems.length) % this.displayItems.length;
    },
    goNextBarber() {
      this.nextBarber();
      this.restartAutoSlide();
    },
    goPrevBarber() {
      this.prevBarber();
      this.restartAutoSlide();
    },
    setActiveIndex(index) {
      this.activeIndex = index;
      this.restartAutoSlide();
    },
    setActiveById(barberId) {
      const idx = this.displayItems.findIndex((item) => item.id === barberId);
      if (idx < 0) return;
      this.activeIndex = idx;
      this.restartAutoSlide();
    },
    profileImage(path) {
      return resolveMediaUrl(path);
    },
    startAutoSlide() {
      if (this.displayItems.length <= 1) return;
      this.autoSlideTimer = setInterval(() => {
        this.nextBarber();
      }, 5000);
    },
    stopAutoSlide() {
      if (!this.autoSlideTimer) return;
      clearInterval(this.autoSlideTimer);
      this.autoSlideTimer = null;
    },
    restartAutoSlide() {
      this.stopAutoSlide();
      this.startAutoSlide();
    },
  },
  async mounted() {
    await Promise.all([this.getAll(), this.loadReviews()]);
    this.restartAutoSlide();
  },
  beforeUnmount() {
    this.stopAutoSlide();
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
  grid-template-columns: 48px minmax(0, 1fr) 48px;
  align-items: center;
  gap: 16px;
  max-width: 1180px;
  margin: 0 auto;
  min-height: 560px;
}

.focus-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(220px, 1fr));
  gap: 18px;
}

.barber-focus-card {
  background: #ececec;
  border: 1px solid #d0d0d0;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.barber-focus-card:hover {
  transform: translateY(-4px);
}

.barber-focus-card.is-active {
  box-shadow: 0 14px 26px rgba(0, 0, 0, 0.15);
  border-color: #bfa06f;
}

.barber-fade-slide-enter-active,
.barber-fade-slide-leave-active {
  transition: opacity 0.42s ease, transform 0.42s ease;
}

.barber-fade-slide-enter-from {
  opacity: 0;
  transform: translateX(18px);
}

.barber-fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-18px);
}

.focus-image-wrap {
  width: 100%;
  height: clamp(180px, 22vw, 300px);
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
  padding: 18px 16px 22px;
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
  line-height: 1.55;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
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
    min-height: auto;
  }

  .focus-grid {
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

@media (max-width: 575.98px) {
  .home-barbers {
    padding: 28px 12px 24px;
  }

  .barber-focus-card {
    display: none;
  }

  .barber-focus-card.is-active {
    display: block;
  }

  .carousel-shell {
    max-width: 100%;
    min-height: auto;
    gap: 10px;
  }

  .focus-content {
    padding: 18px 16px 22px;
  }

  .nav-right {
    margin-top: -40px;
  }
}
</style>

