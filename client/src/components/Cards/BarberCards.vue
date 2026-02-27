<template>
  <div class="row g-4">
    <div
      v-for="item in items"
      :key="item.id"
      class="col-12 col-sm-6 col-lg-4 col-xl-3"
    >
      <article class="barber-card h-100">
        <div class="media-wrap">
          <img
            v-if="item.profilePicture"
            :src="item.profilePicture"
            class="barber-image"
            :alt="`Barber ${item.id}`"
          />
          <div
            v-else
            class="barber-image-placeholder d-flex align-items-center justify-content-center"
          >
            <i class="bi bi-person-circle fs-1"></i>
          </div>
          <span
            class="status-chip"
            :class="item.isActive ? 'status-active' : 'status-inactive'"
          >
            {{ item.isActive ? "Aktiv" : "Inaktiv" }}
          </span>
        </div>

        <div class="card-body-custom d-flex flex-column">
          <h5 class="name mb-1">
            {{ item.user?.name || item.name || `Barber #${item.id}` }}
          </h5>
          <p class="email mb-3">
            {{ item.user?.email || item.email || "" }}
          </p>
          <p class="intro mb-0 flex-grow-1">
            {{ item.introduction || "Nincs bemutatkozas." }}
          </p>
        </div>
      </article>
    </div>
  </div>
</template>

<script>
export default {
  name: "BarberCards",
  props: {
    items: { type: Array, required: true },
  },
};
</script>

<style scoped>
.barber-card {
  border: 1px solid #e9ecef;
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 8px 24px rgba(17, 17, 17, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.barber-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(17, 17, 17, 0.1);
}

.media-wrap {
  position: relative;
  background: #111111;
}

.barber-image,
.barber-image-placeholder {
  width: 100%;
  height: 230px;
  object-fit: cover;
}

.barber-image-placeholder {
  background: linear-gradient(135deg, #f3f4f6, #e9ecef);
  color: #6c757d;
}

.status-chip {
  position: absolute;
  right: 12px;
  bottom: 12px;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid transparent;
}

.status-active {
  background: #111111;
  color: #6cb93f;
  border-color: rgba(197, 160, 89, 0.4);
}

.status-inactive {
  background: #f8f9fa;
  color: #6c757d;
  border-color: #dee2e6;
}

.card-body-custom {
  padding: 16px;
}

.name {
  color: #111111;
  font-weight: 700;
}

.email {
  color: #6c757d;
  font-size: 12px;
}

.intro {
  color: #343a40;
  line-height: 1.45;
}
</style>
