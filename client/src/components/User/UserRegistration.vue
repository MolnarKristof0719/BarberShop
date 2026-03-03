<template>
  <section class="registration-shell">
    <div class="registration-card">
      <div class="registration-card-header">
        <h1 class="title mb-1">Regisztráció</h1>
        <p class="subtitle mb-0">Hozz létre új fiókot pár lépéssel.</p>
      </div>

      <div class="registration-card-body">
        <form
          @submit.prevent="handleSubmit"
          :class="{ 'was-validated': validated }"
          novalidate
        >
          <div class="mb-3">
            <label for="userName" class="form-label">Felhasználónév</label>
            <input
              id="userName"
              v-model="userName"
              type="text"
              class="form-control"
              @input="clearError('name')"
              required
            />
            <div v-if="!serverErrors.name" class="invalid-feedback">
              A név kötelező, es legalább 2 karakter legyen.
            </div>
            <div v-if="serverErrors.name" class="invalid-feedback d-block">
              {{ serverErrors.name[0] }}
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email címed</label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="form-control"
              @input="clearError('email')"
              required
            />
            <div v-if="!serverErrors.email" class="invalid-feedback">
              Az email kötelező, vagy nem szabályos.
            </div>
            <div v-if="serverErrors.email" class="invalid-feedback d-block">
              {{ serverErrors.email[0] }}
            </div>
          </div>

          <PasswordField
            class="mb-3"
            ref="pass1Comp"
            v-model="password"
            :label="'Jelszó'"
            :inputRef="'firstInput'"
            :label-id="'password'"
            :serverErrors="serverErrors"
          />

          <PasswordField
            ref="pass2Comp"
            v-model="confirmPassword"
            :label="'Jelszó mégegyszer'"
            :inputRef="'confirmInput'"
            :label-id="'confirmPassword'"
            :passwordErrorMessage="passwordErrorMessage"
            :serverErrors="serverErrors"
          />

          <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-dark submit-btn">Regisztrálok</button>
            <button
              type="button"
              class="btn btn-outline-dark cancel-btn"
              @click="this.$router.push('/login')"
            >
              Bejelentkezés
            </button>
          </div>
        </form>

        <ToastContainer />
      </div>
    </div>
  </section>
</template>

<script>
import ToastContainer from "../Message/ToastContanier.vue";
import PasswordField from "./PasswordField.vue";

export default {
  name: "UserRegistration",
  components: {
    PasswordField,
    ToastContainer,
  },
  data() {
    return {
      userName: "",
      email: "",
      password: "",
      confirmPassword: "",
      validated: false,
      passwordErrorMessage: "",
      serverErrors: {},
    };
  },
  methods: {
    validatePasswords() {
      const comp2 = this.$refs.pass2Comp;
      const input2 = comp2?.$refs[comp2.inputRef];

      if (this.password !== this.confirmPassword) {
        input2.setCustomValidity("A jelszavak nem egyeznek!");
        this.passwordErrorMessage = "A jelszavak nem egyeznek!";
      } else {
        input2.setCustomValidity("");
        this.passwordErrorMessage = "";
      }
    },
    handleSubmit(event) {
      this.validatePasswords();
      const form = event.target;
      this.validated = true;

      if (form.checkValidity() === false) {
        console.log("Hiba:");
      } else {
        console.log("Sikeres validacio!");
        const data = {
          name: this.userName,
          email: this.email,
          password: this.password,
        };
        this.$emit("createUser", {
          data,
          done: (success) => {
            if (success) {
              this.$router.push("/login");
            } else {
              console.log("Server oldali hiba, urlap marad");
            }
          },
        });
      }
    },
    setServerErrors(errors) {
      this.serverErrors = errors;
    },
    clearError(field) {
      if (this.serverErrors[field]) {
        delete this.serverErrors[field];
      }
    },
  },
};
</script>

<style scoped>
.registration-shell {
  max-width: 520px;
  margin: 24px auto;
  padding: 0 10px;
}

.registration-card {
  border: 1px solid #d9dde2;
  background: #ffffff;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
  border-radius: 16px;
  overflow: hidden;
}

.registration-card-header {
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

.registration-card-body {
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
.cancel-btn {
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

.cancel-btn {
  border-color: #374151;
  color: #374151;
}

.cancel-btn:hover {
  background: #1f2937;
  color: #ffffff;
  border-color: #1f2937;
}

@media (max-width: 575px) {
  .submit-btn,
  .cancel-btn {
    flex: 1;
  }
}
</style>
