<template>
  <div
    class="toast-card"
    :class="{
      'toast-error': type == 'Error',
      'toast-success': type == 'Success',
    }"
    v-if="messages.length"
    role="alert"
    aria-live="polite"
  >
    <div class="toast-content">
      <p v-for="message in messages" :key="message" class="toast-message">
        {{ message }}
      </p>
    </div>
    <button
      type="button"
      class="toast-close"
      @click="close()"
      aria-label="Bezárás"
    >
      &times;
    </button>
  </div>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useToastStore } from "@/stores/toastStore";

export default {
  methods: {
    ...mapActions(useToastStore, ["close"]),
  },
  computed: {
    ...mapState(useToastStore, ["messages", "type"]),
  },
};
</script>

<style scoped>
.toast-card {
  position: fixed;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1080;
  width: min(460px, calc(100vw - 24px));
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px 18px;
  border-radius: 10px;
  border: 1.5px solid #111;
  background: #fff;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
}

.toast-success {
  border-left: 6px solid #111;
}

.toast-error {
  background: #ffe7e7;
  color: #7a1010;
  border-color: #8b1a1a;
  border-left: 6px solid #8b1a1a;
}

.toast-content {
  flex: 1;
}

.toast-message {
  margin: 0;
  font-size: 1.1rem;
  line-height: 1.35;
}

.toast-message + .toast-message {
  margin-top: 6px;
}

.toast-close {
  border: 0;
  background: transparent;
  color: inherit;
  font-size: 1.9rem;
  line-height: 1;
  opacity: 0.55;
  padding: 0;
  margin-top: -2px;
  cursor: pointer;
}

.toast-close:hover {
  opacity: 1;
}
</style>
