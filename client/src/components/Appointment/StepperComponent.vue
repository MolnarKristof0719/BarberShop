<template>
  <div class="stepper">
    <button
      v-for="step in stepItems"
      :key="step.id"
      type="button"
      class="stepper-item"
      :class="{
        active: currentStep === step.id,
        done: currentStep > step.id,
      }"
      :disabled="step.id > currentStep"
      @click="$emit('step-click', step.id)"
    >
      <span class="step-index">{{ step.id }}</span>
      <span class="step-label">{{ step.label }}</span>
    </button>
  </div>
</template>

<script>
export default {
  name: "StepperComponent",
  props: {
    currentStep: { type: Number, required: true },
    stepItems: { type: Array, required: true },
  },
  emits: ["step-click"],
};
</script>
