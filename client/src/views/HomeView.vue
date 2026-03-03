<template>
  <section class="home-page">
    <section class="hero-banner" :style="heroStyle">
      <div class="hero-overlay">
        <h1 class="hero-title-main">BLACK TIMBER BARBERSHOP</h1>
        <RouterLink class="hero-book-btn" to="/appointment">Book Now</RouterLink>
      </div>
    </section>
  </section>
</template>

<script>
import { mapActions } from "pinia";
import { useSearchStore } from "@/stores/searchStore";

export default {
  name: "HomeView",
  data() {
    return {
      heroImageUrl: "/hero/barbershop.jpg",
    };
  },
  computed: {
    heroStyle() {
      return {
        backgroundImage: `url('${this.heroImageUrl}')`,
      };
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord"]),
  },
  mounted() {
    this.resetSearchWord();
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
</style>
