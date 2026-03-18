<template>
  <section class="barber-detail-page">
    <div class="hero">
      <p class="hero-kicker mb-1">Barber Shop</p>
      <h1 class="hero-title mb-1">Barber információ</h1>
      <p class="hero-subtitle mb-0">
        Ismerd meg a barber munkáit és véleményeket.
      </p>
      <RouterLink to="/barber" class="btn btn-outline-dark mt-3 barbersbutton">
        Vissza a barber listához
      </RouterLink>
    </div>

    <div v-if="loading" class="state-box mt-3">Betöltés...</div>
    <div v-else-if="errorMsg" class="state-box mt-3 error-box">
      {{ errorMsg }}
    </div>

    <div v-else-if="barber" class="detail-shell mt-3">
      <section class="profile-card">
        <div class="profile-image-wrap">
          <img
            v-if="barber.profilePicture"
            :src="profileImage(barber.profilePicture)"
            :alt="barberName"
            class="profile-image"
          />
          <div
            v-else
            class="profile-image fallback d-flex align-items-center justify-content-center"
          >
            <i class="bi bi-person-circle fs-1"></i>
          </div>
        </div>
        <div class="profile-content">
          <h2 class="profile-name mb-2">{{ barberName }}</h2>
          <p class="profile-role mb-3">Barber</p>
          <div class="rating-summary">
            <div class="rating-stars">
              <i
                v-for="n in 5"
                :key="n"
                class="bi"
                :class="n <= roundedRating ? 'bi-star-fill' : 'bi-star'"
              ></i>
            </div>
            <p class="rating-text mb-0">
              {{ ratingText }}
            </p>
          </div>
          <p class="profile-intro mb-0">
            {{ barber.introduction || "Nincs bemutatkozás ehhez a barberhez." }}
          </p>
        </div>
      </section>

      <section class="reviews-section">
        <h3 class="section-title">Értékelések és vélemények</h3>
        <div v-if="!reviews.length" class="empty-card">Még nincs vélemény.</div>
        <div v-else class="reviews-grid">
          <article
            v-for="review in reviews"
            :key="review.id"
            class="review-card"
          >
            <div class="review-header">
              <p class="reviewer-name mb-0">
                {{ review.user?.name || "Vendég" }}
              </p>
              <div class="rating-stars small">
                <i
                  v-for="n in 5"
                  :key="`${review.id}-${n}`"
                  class="bi"
                  :class="n <= review.rating ? 'bi-star-fill' : 'bi-star'"
                ></i>
              </div>
            </div>
            <p class="review-text mb-0">
              {{ review.comment || "Nincs szöveges vélemény." }}
            </p>
          </article>
        </div>
      </section>

      <section class="references-section">
        <h3 class="section-title">Referenciaképek</h3>
        <div v-if="!referencePictures.length" class="empty-card">
          Ehhez a barberhez még nincs feltöltött referenciakép.
        </div>
        <div v-else class="references-grid">
          <button
            v-for="(picture, index) in referencePictures"
            :key="picture.id"
            type="button"
            class="reference-tile"
            @click="openLightbox(index)"
          >
            <img :src="profileImage(picture.picture)" alt="Referencia kep" />
          </button>
        </div>
      </section>
    </div>

    <div
      v-if="lightboxOpen"
      class="lightbox-backdrop"
      @click.self="closeLightbox"
    >
      <button
        type="button"
        class="lightbox-close"
        @click="closeLightbox"
        aria-label="Bezárás"
      >
        ×
      </button>
      <button
        type="button"
        class="lightbox-nav prev"
        @click="prevImage"
        aria-label="Előző kép"
      >
        ‹
      </button>
      <img
        :src="currentLightboxSrc"
        class="lightbox-image"
        alt="Referencia kép nagyban"
      />
      <button
        type="button"
        class="lightbox-nav next"
        @click="nextImage"
        aria-label="Következő kép"
      >
        ›
      </button>
      <div class="lightbox-counter">
        {{ lightboxIndex + 1 }} / {{ referencePictures.length }}
      </div>
    </div>
  </section>
</template>

<script>
import barberService from "@/api/barberService";
import { resolveMediaUrl } from "@/utils/media";

export default {
  name: "BarberDetailView",
  data() {
    return {
      barber: null,
      loading: false,
      errorMsg: "",
      lightboxOpen: false,
      lightboxIndex: 0,
    };
  },
  computed: {
    barberId() {
      const parsed = Number(this.$route?.params?.id);
      return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
    },
    barberName() {
      return (
        this.barber?.user?.name ||
        this.barber?.name ||
        (this.barber?.id ? `Barber #${this.barber.id}` : "")
      );
    },
    reviews() {
      return Array.isArray(this.barber?.reviews) ? this.barber.reviews : [];
    },
    referencePictures() {
      if (Array.isArray(this.barber?.referencePictures)) {
        return this.barber.referencePictures;
      }
      if (Array.isArray(this.barber?.reference_pictures)) {
        return this.barber.reference_pictures;
      }
      return [];
    },
    averageRating() {
      if (!this.reviews.length) return 0;
      const sum = this.reviews.reduce(
        (acc, review) => acc + Number(review.rating || 0),
        0,
      );
      return sum / this.reviews.length;
    },
    roundedRating() {
      return Math.round(this.averageRating || 0);
    },
    ratingText() {
      if (!this.reviews.length) return "Még nincs értékelés";
      return `${this.averageRating.toFixed(1)} / 5 (${this.reviews.length} vélemény)`;
    },
    currentLightboxSrc() {
      if (!this.referencePictures.length) return "";
      const picture = this.referencePictures[this.lightboxIndex];
      return picture ? this.profileImage(picture.picture) : "";
    },
  },
  methods: {
    profileImage(path) {
      return resolveMediaUrl(path);
    },
    openLightbox(index) {
      if (!this.referencePictures.length) return;
      this.lightboxIndex = index;
      this.lightboxOpen = true;
      document.body.style.overflow = "hidden";
    },
    closeLightbox() {
      this.lightboxOpen = false;
      document.body.style.overflow = "";
    },
    nextImage() {
      if (!this.referencePictures.length) return;
      this.lightboxIndex =
        (this.lightboxIndex + 1) % this.referencePictures.length;
    },
    prevImage() {
      if (!this.referencePictures.length) return;
      this.lightboxIndex =
        (this.lightboxIndex - 1 + this.referencePictures.length) %
        this.referencePictures.length;
    },
    onKeydown(event) {
      if (!this.lightboxOpen) return;
      if (event.key === "Escape") this.closeLightbox();
      if (event.key === "ArrowRight") this.nextImage();
      if (event.key === "ArrowLeft") this.prevImage();
    },
    async loadBarber() {
      if (!this.barberId) {
        this.errorMsg = "Hiányzik a barber azonosítója.";
        return;
      }
      this.loading = true;
      this.errorMsg = "";
      try {
        const response = await barberService.getById(this.barberId);
        this.barber = response?.data || null;
      } catch (err) {
        this.errorMsg = "Nem sikerült betölteni a barber adatait.";
      } finally {
        this.loading = false;
      }
    },
  },
  async mounted() {
    await this.loadBarber();
    window.addEventListener("keydown", this.onKeydown);
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.onKeydown);
    document.body.style.overflow = "";
  },
  watch: {
    "$route.params.id": {
      async handler() {
        await this.loadBarber();
      },
    },
  },
};
</script>

<style scoped>
.barbersbutton {
  border-radius: 50px;
  font-size: 12px;
}
.barber-detail-page {
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

.detail-shell {
  display: grid;
  gap: 20px;
}

.profile-card {
  display: grid;
  gap: 16px;
  grid-template-columns: minmax(180px, 260px) 1fr;
  border: 1px solid #e4e8ec;
  border-radius: 16px;
  background: #ffffff;
  overflow: hidden;
}

.profile-image-wrap {
  background: #111111;
  min-height: 220px;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-content {
  padding: 20px;
}

.profile-name {
  font-weight: 700;
  color: #111111;
}

.profile-role {
  color: #6c757d;
  font-style: italic;
}

.rating-summary {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.rating-stars {
  color: #c5a059;
  display: flex;
  gap: 6px;
}

.rating-stars.small {
  font-size: 0.9rem;
}

.rating-text {
  color: #4f4f4f;
  font-weight: 600;
}

.profile-intro {
  color: #222;
  line-height: 1.6;
}

.section-title {
  font-weight: 700;
  color: #111111;
  margin-bottom: 12px;
}

.reviews-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.review-card {
  border: 1px solid #e4e8ec;
  border-radius: 12px;
  background: #ffffff;
  padding: 14px;
  box-shadow: 0 6px 16px rgba(17, 17, 17, 0.05);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.reviewer-name {
  font-weight: 700;
  color: #111111;
}

.review-text {
  color: #4f4f4f;
  line-height: 1.5;
}

.references-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.reference-tile {
  border: 0;
  padding: 0;
  cursor: pointer;
  text-align: left;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e9ecef;
  background: #f8f9fa;
}

.reference-tile img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.empty-card {
  border: 1px dashed #d5dce3;
  border-radius: 12px;
  padding: 16px;
  background: #f8fafc;
  color: #6c757d;
}

.lightbox-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: grid;
  place-items: center;
  z-index: 2000;
}

.lightbox-image {
  max-width: min(92vw, 1200px);
  max-height: 82vh;
  object-fit: contain;
  border-radius: 8px;
  background: #111111;
}

.lightbox-close {
  position: absolute;
  top: 16px;
  right: 20px;
  background: transparent;
  border: 0;
  color: #ffffff;
  font-size: 32px;
  cursor: pointer;
}

.lightbox-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 0;
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 30px;
  cursor: pointer;
}

.lightbox-nav.prev {
  left: 16px;
}

.lightbox-nav.next {
  right: 16px;
}

.lightbox-counter {
  position: absolute;
  bottom: 16px;
  color: #e6e6e6;
  font-size: 0.9rem;
  letter-spacing: 0.04em;
}

@media (max-width: 991.98px) {
  .profile-card {
    grid-template-columns: 1fr;
  }

  .profile-image-wrap {
    height: 240px;
  }
}
</style>
