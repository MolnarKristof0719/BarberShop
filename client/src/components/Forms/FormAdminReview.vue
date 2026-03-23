<template>
  <div>
    <Modal ref="modal" :title="title" @yesEvent="yesEventHandler">
      
      <div class="mb-3 row">
        <label for="comment" class="col-form-label col-3">Vélemény</label>
        <div class="col-9">
          <textarea
            id="comment"
            v-model="formItem.comment"
            class="form-control"
            required
            @input="clearError('comment')"
          ></textarea>
          <div v-if="serverErrors.comment" class="invalid-feedback d-block">
            {{ serverErrors.comment[0] }}
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";

export default {
  name: "FormAdminReview",
  components: {
    Modal,
  },
  emits: ["yesEventForm"],
  props: {
    title: { type: String, default: "Vélemény szerkesztése" },
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

