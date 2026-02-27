<template>
  <section class="account-page">
    <div class="account-card">
      <div class="avatar-large">
        <i class="bi bi-person"></i>
      </div>

      <h1 class="mb-1">Fiok</h1>
      <p class="text-muted mb-4">Bejelentkezett felhasznalo adatai</p>

      <div class="row gy-3">
        <div class="col-12">
          <div class="info-box">
            <small class="label">Nev</small>
            <p class="value mb-0">{{ item?.name || "-" }}</p>
          </div>
        </div>
        <div class="col-12">
          <div class="info-box">
            <small class="label">Email</small>
            <p class="value mb-0">{{ item?.email || "-" }}</p>
          </div>
        </div>
        <div class="col-12">
          <div class="info-box">
            <small class="label">Szerepkor</small>
            <p class="value mb-0">{{ roleLabel }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  name: "AccountView",
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "role"]),
    roleLabel() {
      const labels = {
        1: "Admin",
        2: "Barber",
        3: "User",
      };
      return labels[this.role] || "-";
    },
  },
};
</script>

<style scoped>
.account-page {
  min-height: 100%;
  display: flex;
  justify-content: center;
  padding: 24px 10px;
}

.account-card {
  width: 100%;
  max-width: 560px;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(17, 17, 17, 0.08);
  padding: 22px;
}

.avatar-large {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #111111;
  color: #c5a059;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  margin-bottom: 16px;
}

.info-box {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 10px 12px;
  background: #f8f9fa;
}

.label {
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.value {
  color: #111111;
  font-weight: 600;
}
</style>
