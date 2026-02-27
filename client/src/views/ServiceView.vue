<template>
  <section class="service-page">
    <div class="service-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <p class="hero-kicker mb-1">Barber Shop</p>
        <h1 class="hero-title mb-1">Szolgaltatasok</h1>
        <p class="hero-subtitle mb-0">Valaszd ki a neked megfelelo szolgaltatast.</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-4 text-secondary"
          aria-hidden="true"
        ></i>
        <span class="count-pill">{{ filteredItems.length }} szolgaltatas</span>
      </div>
    </div>

    <div class="service-content mt-4" v-if="filteredItems.length > 0">
      <ServiceCards :items="filteredItems" />
    </div>
    <div v-else class="empty-state mt-4">Nincs talalat.</div>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useServiceStore } from "@/stores/serviceStore";
import { useSearchStore } from "@/stores/searchStore";
import ServiceCards from "@/components/Cards/ServiceCards.vue";

export default {
  name: "ServiceView",
  components: {
    ServiceCards,
  },
  computed: {
    ...mapState(useServiceStore, ["items", "loading"]),
    ...mapState(useSearchStore, ["searchWord"]),
    filteredItems() {
      const q = (this.searchWord || "").toLowerCase().trim();
      if (!q) {
        return this.items;
      }
      return this.items.filter((item) =>
        String(item.service || "").toLowerCase().includes(q),
      );
    },
  },
  methods: {
    ...mapActions(useServiceStore, ["getAll"]),
    ...mapActions(useSearchStore, ["resetSearchWord"]),
  },
  async mounted() {
    this.resetSearchWord();
    await this.getAll();
  },
};
</script>

<style scoped>
.service-page {
  min-height: 100%;
  padding: 10px;
  background:
    repeating-linear-gradient(
      45deg,
      rgba(197, 160, 89, 0.08),
      rgba(197, 160, 89, 0.08) 2px,
      transparent 2px,
      transparent 12px
    ),
    #f7f5f1;
  border-radius: 14px;
}

.service-hero {
  border: 1px solid #eadfe1;
  border-radius: 16px;
  padding: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.hero-kicker {
  font-size: 12px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #c5a059;
  font-weight: 700;
}

.hero-title {
  font-size: clamp(1.5rem, 2.4vw, 2rem);
  color: #111111;
  font-weight: 700;
}

.hero-subtitle {
  color: #6c757d;
}

.count-pill {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid #e2d6d8;
  background: #fff;
  color: #111111;
  font-weight: 600;
}

.empty-state {
  width: 100%;
  text-align: center;
  padding: 30px;
  border: 1px dashed #d6d9de;
  border-radius: 12px;
  color: #6c757d;
  background: #ffffff;
}
</style>
