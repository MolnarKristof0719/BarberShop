<template>
  <div class="row g-4">
    <div v-for="item in items" :key="item.id" class="col-12 col-md-6">
      <article class="service-card h-100">
        <h5 class="service-title mb-2">{{ item.service }}</h5>
        <p class="price mb-4">{{item.price}} Ft</p>

      
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
    
};
</script>

<style scoped>
.service-card {
  border: 1px solid #e5d7d9;
  border-radius: 14px;
  background: #f8f8f8;
  padding: 22px;
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.05);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(17, 17, 17, 0.09);
}



.service-title {
  color: #111111;
  font-weight: 700;
  font-size: 25px;
}

.service-desc {
  color: #5f646d;
  line-height: 1.45;
}

.price {
  color: #8f2438;
  font-size: 20px;
  font-weight: 700;
  line-height: 1;
}

.time {
  color: #5f646d;
  margin-top: 4px;
}

@media (max-width: 575.98px) {
  .service-card {
    padding: 16px;
  }

  .service-title {
    font-size: 20px;
  }

  .price {
    font-size: 18px;
  }
}
</style>
