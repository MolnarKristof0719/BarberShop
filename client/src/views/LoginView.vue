<template>
  <section class="login-page">
    <UserLogin @logIn="loginHandler" />
  </section>
</template>

<script>
import { mapActions } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import UserLogin from "@/components/User/UserLogin.vue";

export default {
  name: "LoginView",
  components: {
    UserLogin,
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["login"]),
    async loginHandler(user) {
      try {
        await this.login(user);
        this.$router.push("/");
      } catch (error) {
        console.log("Bejelentkezesi hiba!");
      }
    },
  },
};
</script>

<style scoped>
.login-page {
  min-height: 100%;
  padding: 8px 0 20px;
}
</style>
