<template>
  <div>
    <div class="d-flex align-items-center m-0 mb-2">
      <h1>{{ pageTitle }}</h1>
      <div class="d-flex align-items-center m-0 ms-2">
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-3 col-auto p-0 pe-1"
        ></i>
        <p class="m-0 ms-2">({{ getItemsLength }})</p>
      </div>
    </div>

    <GenericTable
      :items="displayItems"
      :columns="tableColumns"
      :useCollectionStore="useCollectionStore"
      :cButtonVisible="false"
      :uButtonVisible="false"
      @delete="deleteHandler"
      @sort="sortHandler"
      v-if="items.length > 0"
    />
    <div v-else style="width: 120px" class="m-auto">Nincs talalat</div>

    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useBarberOffDayStore } from "@/stores/barberOffDayStore";
import { useSearchStore } from "@/stores/searchStore";
import { useBarberStore } from "@/stores/barberStore";
import GenericTable from "@/components/Table/GenericTable.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";

export default {
  name: "AdminDayOffsView",
  components: {
    GenericTable,
    ConfirmModal,
  },
  watch: {
    searchWord() {
      this.getAllSortSearch(this.sortColumn, this.sortDirection);
    },
  },
  data() {
    return {
      pageTitle: "Admin - Szabadnapok (tablazat)",
      tableColumns: [
        { key: "id", label: "ID", debug: 2 },
        { key: "barberId", label: "Barber", debug: 2 },
        { key: "offDay", label: "Szabadnap", debug: 2 },
      ],
      useCollectionStore: useBarberOffDayStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
    };
  },
  computed: {
    ...mapState(useBarberOffDayStore, [
      "items",
      "loading",
      "sortColumn",
      "sortDirection",
      "getItemsLength",
    ]),
    ...mapState(useBarberStore, { barberItems: "items" }),
    ...mapState(useSearchStore, ["searchWord"]),
    barberNameById() {
      const map = {};
      (this.barberItems || []).forEach((barber) => {
        if (!barber?.id) return;
        map[barber.id] = barber.user?.name || "";
      });
      return map;
    },
    displayItems() {
      return (this.items || []).map((row) => ({
        ...row,
        barberId: this.barberNameById[row.barberId] || row.barberId,
        offDay: this.formatOffDay(row.offDay),
      }));
    },
  },
  methods: {
    ...mapActions(useBarberOffDayStore, [
      "getAll",
      "getAllSortSearch",
      "delete",
    ]),
    ...mapActions(useBarberStore, { getAllBarbers: "getAll" }),
    ...mapActions(useSearchStore, ["resetSearchWord"]),
    sortHandler(column) {
      this.getAllSortSearch(column);
    },
    deleteHandler(id) {
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    cancelHandler() {
      this.isOpenConfirmModal = false;
    },
    async confirmHandler() {
      try {
        await this.delete(this.toDeleteId);
      } catch (error) {}
      this.isOpenConfirmModal = false;
    },
    formatOffDay(value) {
      const raw = String(value || "");
      if (!raw) return "";
      const dateOnly = raw.includes("T") ? raw.slice(0, 10) : raw;
      const parts = dateOnly.split("-");
      if (parts.length === 3) {
        const [year, month, day] = parts;
        if (year && month && day) {
          return `${year}.${month}.${day}.`;
        }
      }
      return dateOnly;
    },
  },
  async mounted() {
    this.resetSearchWord();
    await this.getAllBarbers();
    await this.getAll();
  },
};
</script>

<style></style>
