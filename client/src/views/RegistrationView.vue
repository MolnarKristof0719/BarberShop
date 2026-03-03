<template>
  <section class="registration-page">
    <UserRegistration ref="form" @createUser="handlerCreateUser" />
  </section>
</template>

<script>
import { mapActions } from "pinia";
import { useUserStore } from "@/stores/userStore";
import UserRegistration from "@/components/User/UserRegistration.vue";

export default {
  name: "RegistrationView",
  components: {
    UserRegistration,
  },
  methods: {
    ...mapActions(useUserStore, ["createUser"]),
    async handlerCreateUser({ data, done }) {
      try {
        await this.createUser(data);
        done(true);
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.$refs.form.setServerErrors(err.response.data.errors);
          done(false);
        } else {
          done(false);
        }
      }
    },
  },
};
</script>

<style scoped>
.registration-page {
  min-height: 100%;
  padding: 8px 0 20px;
}
</style>
