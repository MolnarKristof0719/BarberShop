<template>
  <section class="barber-page">
    <div class="barber-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <p class="hero-kicker mb-1">Barber Shop</p>
        <h1 class="hero-title mb-1">Barberek</h1>
        <p class="hero-subtitle mb-0">Valaszd ki a megfelelo szakembert.</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-4 text-secondary"
          aria-hidden="true"
        ></i>
        <span class="count-pill">{{ getItemsLength }} barber</span>
      </div>
    </div>

    <div class="barber-content mt-4">
      <BarberCards :items="items" v-if="items.length > 0" />
      <div v-else class="empty-state">Nincs talalat.</div>
    </div>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useBarberStore } from "@/stores/barberStore";
import { useSearchStore } from "@/stores/searchStore";
import BarberCards from "@/components/Cards/BarberCards.vue";

export default {
  name: "BarberView",
  components: {
    BarberCards,
  },
  computed: {
    ...mapState(useBarberStore, ["items", "loading", "getItemsLength"]),
  },
  methods: {
    ...mapActions(useBarberStore, ["getAll"]),
    ...mapActions(useSearchStore, ["resetSearchWord"]),
  },
  async mounted() {
    this.resetSearchWord();
    await this.getAll();
  },
};
</script>

<style scoped>
.barber-page {
  min-height: 100%;
  padding: 10px;
}

.barber-hero {
  border: 1px solid #e9ecef;
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
  border: 1px solid #dfe3e8;
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
