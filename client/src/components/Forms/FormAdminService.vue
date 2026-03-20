<template>
  <div>
    <Modal ref="modal" :title="title" @yesEvent="yesEventHandler">
      
      <div class="mb-3 row">
        <label for="service" class="col-form-label col-3">Szolgáltatás</label>
        <div class="col-9">
          <input
            id="service"
            v-model="formItem.service"
            class="form-control"
            required
            @input="clearError('service')"
          />
          <div v-if="serverErrors.service" class="invalid-feedback d-block">
            {{ serverErrors.service[0] }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label for="price" class="col-form-label col-3">Ár (Ft)</label>
        <div class="col-9">
          <input
            id="price"
            type="number"
            min="0"
            step="1"
            v-model.number="formItem.price"
            class="form-control"
            required
            @input="clearError('price')"
          />
          <div v-if="serverErrors.price" class="invalid-feedback d-block">
            {{ serverErrors.price[0] }}
          </div>
        </div>
      </div>
     
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";

export default {
  name: "FormAdminService",
  components: {
    Modal,
  },
  emits: ["yesEventForm"],
  props: {
    title: { type: String, default: "Szolgáltatás szerkesztése" },
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
