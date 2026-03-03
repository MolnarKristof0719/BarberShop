<template>
  <section class="login-shell">
    <div class="login-card">
      <div class="login-card-header">
        <h1 class="title mb-1">Bejelentkezés</h1>
        <p class="subtitle mb-0">Lépj be, vagy hozz létre új fiókot.</p>
      </div>

      <div class="login-card-body">
        <form
          @submit.prevent="handleSubmit"
          :class="{ 'was-validated': validated }"
          novalidate
        >
          <div class="mb-3">
            <label for="email" class="form-label">Email címed</label>
            <input
              id="email"
              v-model="user.email"
              type="email"
              class="form-control"
              required
            />
            <div class="invalid-feedback">Az email üres, vagy helytelen.</div>
          </div>

          <PasswordField
            class="mt-3"
            v-model="user.password"
            :label="'Jelszó'"
            :label-id="'password'"
          />

          <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-dark submit-btn">Bejelentkezés</button>
            <RouterLink to="/registration" class="btn btn-outline-dark register-btn">
              Regisztráció
            </RouterLink>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import PasswordField from "./PasswordField.vue";

class User {
  constructor(email = "", password = "") {
    this.email = email;
    this.password = password;
  }
}

export default {
  name: "UserLogin",
  components: {
    PasswordField,
  },
  data() {
    return {
      password: "",
      email: "",
      validated: false,
      user: new User(),
    };
  },
  methods: {
    handleSubmit(event) {
      const form = event.target;
      this.validated = true;

      if (form.checkValidity() === false) {
        console.log("Hiba:");
      } else {
        console.log("Sikeres validacio!");
        this.$emit("logIn", this.user);
      }
    },
  },
};
</script>

<style scoped>
.login-shell {
  max-width: 520px;
  margin: 24px auto;
  padding: 0 10px;
}

.login-card {
  border: 1px solid #d9dde2;
  background: #ffffff;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
  border-radius: 16px;
  overflow: hidden;
}

.login-card-header {
  padding: 22px 22px 16px;
  border-bottom: 1px solid #e7eaee;
  background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 100%);
}


.title {
  font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande", "Lucida Sans", Arial, sans-serif;
  font-size: clamp(1.5rem, 2.2vw, 2rem);
  color: #111827;
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.login-card-body {
  padding: 20px 22px 24px;
}

.form-label {
  color: #252525;
  font-weight: 600;
}

.form-control {
  border-radius: 12px;
  border-color: #d1d5db;
  min-height: 42px;
}

.form-control:focus {
  border-color: #111827;
  box-shadow: 0 0 0 0.2rem rgba(17, 24, 39, 0.12);
}

:deep(.input-group .btn) {
  border-radius: 12px;
}

.submit-btn,
.register-btn {
  border-radius: 12px;
  min-width: 120px;
}

.submit-btn {
  background: #111827;
  border-color: #111827;
}

.submit-btn:hover {
  background: #0b1220;
  border-color: #0b1220;
}

.register-btn {
  border-color: #374151;
  color: #374151;
}

.register-btn:hover {
  background: #1f2937;
  color: #ffffff;
  border-color: #1f2937;
}

@media (max-width: 575px) {
  .submit-btn,
  .register-btn {
    flex: 1;
  }
}
</style>
