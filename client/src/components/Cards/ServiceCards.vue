<template>
  <div class="row g-4">
    <div
      v-for="item in items"
      :key="item.id"
      class="col-12 col-md-6 col-xl-4"
    >
      <article class="service-card h-100">
        <div class="service-icon">
          <i class="bi" :class="iconById(item.id)"></i>
        </div>

        <h5 class="service-title mb-2">{{ item.service }}</h5>
        <p class="service-desc mb-4">{{ descriptionByName(item.service) }}</p>

        <div class="service-meta">
          <p class="price mb-0">{{ priceById(item.id) }} Ft</p>
          <p class="time mb-0">{{ durationById(item.id) }} perc</p>
        </div>
      </article>
    </div>
  </div>
</template>

<script>
export default {
  name: "ServiceCards",
  props: {
    items: { type: Array, required: true },
  },
  methods: {
    iconById(id) {
      const icons = ["bi-scissors", "bi-stars", "bi-droplet", "bi-magic"];
      return icons[id % icons.length];
    },
    priceById(id) {
      const prices = [3500, 4500, 5500, 6500, 7500, 8500];
      return prices[id % prices.length];
    },
    durationById(id) {
      const durations = [20, 30, 40, 50, 60];
      return durations[id % durations.length];
    },
    descriptionByName(name) {
      return `${name} szolgaltatas professzionalis kivitelezessel.`;
    },
  },
};
</script>

<style scoped>
.service-card {
  border: 1px solid #e5d7d9;
  border-radius: 14px;
  background: #ffffff;
  padding: 22px;
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(17, 17, 17, 0.09);
}

.service-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #8f2438;
  color: #d3ad47;
  border: 3px solid #b16e3e;
  font-size: 24px;
  margin-bottom: 16px;
}

.service-title {
  color: #111111;
  font-weight: 700;
}

.service-desc {
  color: #5f646d;
  line-height: 1.45;
}

.price {
  color: #8f2438;
  font-size: 32px;
  font-weight: 700;
  line-height: 1;
}

.time {
  color: #5f646d;
  margin-top: 4px;
}
</style>
