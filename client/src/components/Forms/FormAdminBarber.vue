<template>
  <div>
    <Modal ref="modal" :title="title" @yesEvent="yesEventHandler">
      <div class="mb-3 row pt-2">
        <label for="userId" class="col-form-label col-3">User ID</label>
        <div class="col-9">
          <input
            id="userId"
            v-model.number="formItem.userId"
            type="number"
            class="form-control"
            min="1"
            required
            @input="clearError('userId')"
          />
          <div v-if="serverErrors.userId" class="invalid-feedback d-block">
            {{ serverErrors.userId[0] }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label for="profilePicture" class="col-form-label col-3">Profilkep URL</label>
        <div class="col-9">
          <input
            id="profilePicture"
            v-model="formItem.profilePicture"
            type="text"
            class="form-control"
            required
            @input="clearError('profilePicture')"
          />
          <div v-if="serverErrors.profilePicture" class="invalid-feedback d-block">
            {{ serverErrors.profilePicture[0] }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label for="introduction" class="col-form-label col-3">Bemutatkozas</label>
        <div class="col-9">
          <textarea
            id="introduction"
            v-model="formItem.introduction"
            class="form-control"
            rows="4"
            required
            @input="clearError('introduction')"
          ></textarea>
          <div v-if="serverErrors.introduction" class="invalid-feedback d-block">
            {{ serverErrors.introduction[0] }}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="offset-3 col-9">
          <div class="form-check">
            <input
              id="isActive"
              v-model="formItem.isActive"
              type="checkbox"
              class="form-check-input"
            />
            <label for="isActive" class="form-check-label">Aktiv</label>
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";

export default {
  name: "FormAdminBarber",
  components: {
    Modal,
  },
  emits: ["yesEventForm"],
  props: {
    title: { type: String, default: "Barber szerkesztes" },
    item: { type: Object, required: true },
  },
  data() {
    return {
      formItem: { ...this.item },
      serverErrors: {},
    };
  },
  watch: {
    item(value) {
      this.formItem = { ...value };
    },
  },
  methods: {
    show() {
      this.serverErrors = {};
      this.$refs.modal.show();
    },
    setServerErrors(errors) {
      this.serverErrors = errors;
    },
    clearError(field) {
      if (this.serverErrors[field]) {
        delete this.serverErrors[field];
      }
    },
    yesEventHandler(done) {
      this.$emit("yesEventForm", { item: this.formItem, done });
    },
  },
};
</script>

<style></style>
