<template>
  <div>
    <div class="d-flex align-items-center m-0 mb-2">
      <h1>{{ pageTitle }}</h1>
      <div class="d-flex align-items-center m-0 ms-2">
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-3 col-auto p-0 pe-1"
        ></i>
        <ButtonsCrudCreate v-if="!loading" @create="createHandler" />
        <p class="m-0 ms-2">({{ getItemsLength }})</p>
      </div>
    </div>

    <GenericTable
      :items="items"
      :columns="tableColumns"
      :useCollectionStore="useCollectionStore"
      :cButtonVisible="false"
      @delete="deleteHandler"
      @update="updateHandler"
      @sort="sortHandler"
      v-if="items.length > 0"
    />
    <div v-else style="width: 120px" class="m-auto">Nincs talalat</div>

    <FormService
      ref="form"
      :title="title"
      :item="item"
      @yesEventForm="yesEventFormHandler"
    />

    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useServiceStore } from "@/stores/serviceStore";
import { useSearchStore } from "@/stores/searchStore";
import GenericTable from "@/components/Table/GenericTable.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
import FormService from "@/components/Forms/FormService.vue";

export default {
  name: "AdminServicesView",
  components: {
    GenericTable,
    ConfirmModal,
    ButtonsCrudCreate,
    FormService,
  },
  watch: {
    searchWord() {
      this.getAllSortSearch(this.sortColumn, this.sortDirection);
    },
  },
  data() {
    return {
      pageTitle: "Admin - Szolgaltatasok (tablazat)",
      tableColumns: [
        { key: "id", label: "ID", debug: 2 },
        { key: "service", label: "Szolgaltatas", debug: 2 },
      ],
      useCollectionStore: useServiceStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r",
      title: "",
    };
  },
  computed: {
    ...mapState(useServiceStore, [
      "item",
      "items",
      "loading",
      "sortColumn",
      "sortDirection",
      "getItemsLength",
    ]),
    ...mapState(useSearchStore, ["searchWord"]),
  },
  methods: {
    ...mapActions(useServiceStore, [
      "getAll",
      "getAllSortSearch",
      "getById",
      "create",
      "update",
      "delete",
      "clearItem",
    ]),
    ...mapActions(useSearchStore, ["resetSearchWord"]),
    sortHandler(column) {
      this.getAllSortSearch(column);
    },
    deleteHandler(id) {
      this.state = "d";
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    updateHandler(id) {
      this.state = "u";
      this.title = "Szolgaltatas modositasa";
      this.getById(id);
      this.$refs.form.show();
    },
    createHandler() {
      this.state = "c";
      this.title = "Uj szolgaltatas";
      this.clearItem();
      this.$refs.form.show();
    },
    cancelHandler() {
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async confirmHandler() {
      try {
        await this.delete(this.toDeleteId);
      } catch (error) {}
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async yesEventFormHandler({ item, done }) {
      try {
        if (this.state === "c") {
          await this.create(item);
        } else {
          await this.update(item.id, item);
        }
        this.state = "r";
        done(true);
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.$refs.form.setServerErrors(err.response.data.errors);
        }
        done(false);
      }
    },
  },
  async mounted() {
    this.resetSearchWord();
    await this.getAll();
  },
};
</script>

<style></style>
