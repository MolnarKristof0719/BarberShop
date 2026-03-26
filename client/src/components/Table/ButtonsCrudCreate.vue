<template>
  <div>
    <button type="button" class="btn btn-sm btn-admin btn-admin-create"
        @click="onClickButton()"
    >
        <i class="bi bi-plus-lg"></i>
    </button>
  </div>
</template>

<script>
export default {
    name: 'ButtonsCrudCreate',
    methods: {
        onClickButton(){
            this.$emit('create');
        }
    }

}
</script>

<style>

</style>
