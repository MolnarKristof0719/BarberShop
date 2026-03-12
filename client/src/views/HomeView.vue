<template>
  <section class="home-page">
    <section class="hero-banner" :style="heroStyle">
      <div class="hero-overlay">
        <h1 class="hero-title-main">BLACK TIMBER BARBER SHOP</h1>
        <RouterLink class="hero-book-btn" to="/appointment">Book Now</RouterLink>
      </div>
    </section>
    <section class="services-offered">
      <div class="services-inner">
        <p class="services-eyebrow">BLACK TIMBER BARBER SHOP</p>
        <h2 class="services-title">Szolgáltatások</h2>
        <ul class="services-list">
          <li v-for="service in services" :key="service">
            {{ service }}
          </li>
        </ul>
        <RouterLink class="services-btn" to="/services">További információ</RouterLink>
      </div>
    </section>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useServiceStore } from "@/stores/serviceStore";


export default {
  name: "HomeView",
  data() {
    return {
      heroImageUrl: "/hero/barbershop.jpg",
    };
  },
  computed: {
    ...mapState(useServiceStore, ["items"]),
    heroStyle() {
      return {
        backgroundImage: `url('${this.heroImageUrl}')`,
      };
    },
    services() {
      return (this.items || [])
        .map((service) => service.service)
        .filter(Boolean);
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord"]),
    ...mapActions(useServiceStore, { fetchServices: "getAll" }),
  },
  mounted() {
    this.resetSearchWord();
    this.fetchServices();
  },
};
</script>

<style scoped>
.home-page {
  min-height: 100%;
}

.hero-banner {
  position: relative;
  height: clamp(360px, 48vw, 620px);
  border-radius: 0;
  overflow: hidden;
  background-size: cover;
  background-position: center center;
  background-color: #2a2a2a;
}

.hero-overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(232, 226, 216, 0.72);
  backdrop-filter: blur(2px);
  padding: 34px 18px 36px;
  text-align: center;
}

.hero-title-main {
  margin: 0;
  color: #1f1f1f;
  font-family: Georgia, "Times New Roman", serif;
  font-size: clamp(1.8rem, 4vw, 4rem);
  letter-spacing: 0.03em;
}

.hero-book-btn {
  margin-top: 18px;
  display: inline-block;
  background: #111111;
  color: #ffffff;
  text-decoration: none;
  border: 1px solid #111111;
  border-radius: 0;
  padding: 10px 24px;
  font-size: 0.95rem;
}

.hero-book-btn:hover {
  background: #000000;
  color: #ffffff;
}

.services-offered {
  margin: 24px 0 0;
  padding: clamp(48px, 6vw, 80px) 16px;
  background:
    linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)),
    repeating-linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.25) 0px,
      rgba(255, 255, 255, 0.25) 6px,
      rgba(255, 255, 255, 0) 6px,
      rgba(255, 255, 255, 0) 14px
    ),
    #e3e0da;
}

.services-inner {
  max-width: 720px;
  margin: 0 auto;
  text-align: center;
}

.services-eyebrow {
  margin: 0 0 8px;
  font-size: 0.75rem;
  letter-spacing: 0.3em;
  color: #7a736b;
}

.services-title {
  margin: 0 0 18px;
  font-family: Georgia, "Times New Roman", serif;
  font-size: clamp(1.6rem, 3.2vw, 2.6rem);
  letter-spacing: 0.08em;
  color: #111111;
}

.services-list {
  margin: 0 auto 20px;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 8px;
  color: #1f1f1f;
  font-size: 0.98rem;
}

.services-list li {
  padding: 4px 0;
}

.services-btn {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  text-decoration: none;
  border: 1px solid #111111;
  padding: 10px 24px;
  font-size: 0.95rem;
}

.services-btn:hover {
  background: #000000;
  color: #ffffff;
}
</style>
