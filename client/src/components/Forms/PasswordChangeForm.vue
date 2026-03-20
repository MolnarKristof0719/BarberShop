<template>
  <div>
    <Modal ref="modal" :title="title" @yesEvent="yesEventHandler">
      <div class="mb-4 row pt-2">
        <label for="newpassword" class="col-form-label col-auto pt-1 pe-0">
          Új jelszó:
        </label>
        <div class="col">
          <input
            type="password"
            class="form-control"
            id="newpassword"
            v-model="formItem.newpassword"
            @input="clearError('newpassword')"
            minlength="3"
            required
          />
          <div
            v-if="!serverErrors.newpassword"
            class="invalid-feedback position-absolute"
          >
            Az új jelszó kötelező (min. 3 karakter)
          </div>
          <div
            v-if="serverErrors.newpassword"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.newpassword[0] }}
          </div>
        </div>
      </div>

      <div class="mb-3 row pt-2">
        <label for="newpassword_confirmation" class="col-form-label col-auto pt-1 pe-0">
          Új jelszó újra:
        </label>
        <div class="col">
          <input
            type="password"
            class="form-control"
            id="newpassword_confirmation"
            v-model="formItem.newpassword_confirmation"
            @input="clearError('newpassword_confirmation')"
            minlength="3"
            required
          />
          <div
            v-if="!serverErrors.newpassword_confirmation"
            class="invalid-feedback position-absolute"
          >
            Az új jelszó megerősítése kötelező
          </div>
          <div
            v-if="serverErrors.newpassword_confirmation"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.newpassword_confirmation[0] }}
          </div>
        </div>
      </div>

      <div v-if="clientError" class="text-danger small">
        {{ clientError }}
      </div>
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";

export default {
  emits: ["yesEventForm"],
  name: "PasswordChangeForm",
  components: {
    Modal,
  },
  props: {
    title: { type: String, default: "Jelszó módosítása" },
  },
  data() {
    return {
      formItem: {
        newpassword: "",
        newpassword_confirmation: "",
      },
      serverErrors: {},
      clientError: "",
    };
  },
  methods: {
    show() {
      this.resetForm();
      this.$refs.modal.show();
    },
    hide() {
      this.$refs.modal.hide();
    },
    resetForm() {
      this.formItem = {
        newpassword: "",
        newpassword_confirmation: "",
      };
      this.serverErrors = {};
      this.clientError = "";
    },
    setServerErrors(errors) {
      this.serverErrors = errors || {};
    },
    clearError(field) {
      if (this.serverErrors[field]) {
        delete this.serverErrors[field];
      }
      this.clientError = "";
    },
    yesEventHandler(done) {
      if (this.formItem.newpassword !== this.formItem.newpassword_confirmation) {
        this.clientError = "A két jelszó nem egyezik.";
        done(false);
        return;
      }
      this.$emit("yesEventForm", { item: { ...this.formItem }, done });
    },
  },
};
</script>

<style></style>
