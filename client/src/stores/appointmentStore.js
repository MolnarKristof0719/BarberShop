import { defineStore } from "pinia";
import { useSearchStore } from "./searchStore";
import service from "@/api/appointmentService";

class Item {
  constructor(
    id = 0,
    barberId = "",
    userId = "",
    appointmentDate = "",
    appointmentTime = "",
    status = "",
    cancelledBy = "",
  ) {
    this.id = id;
    this.barberId = barberId;
    this.userId = userId;
    this.appointmentDate = appointmentDate;
    this.appointmentTime = appointmentTime;
    this.status = status;
    this.cancelledBy = cancelledBy;
  }
}

export const useAppointmentStore = defineStore("appointment", {
  state: () => ({
    item: new Item(),
    items: [new Item()],
    loading: false,
    error: null,
    sortColumn: "id",
    sortDirection: "asc",
    searchStore: useSearchStore(),
  }),
  getters: {
    getItemsLength() {
      return this.items.length;
    },
  },
  actions: {
    clearItem() {
      this.item = new Item();
    },

    async getAllAbc() {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAllAbc();
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async getAllSortSearch(column = "id", direction = null) {
      this.loading = true;
      this.error = null;
      this.sortColumn = column;
      if (!direction) {
        direction =
          this.sortColumn === column && this.sortDirection === "asc"
            ? "desc"
            : "asc";
      }
      this.sortDirection = direction;
      try {
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async getAll() {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAll();
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async getById(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getById(id);
        this.item = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async create(data) {
      this.loading = true;
      this.error = null;
      try {
        await service.create(data);
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
        return true;
      } catch (err) {
        this.error =
          err?.response?.data?.message ||
          err?.response?.data?.errors ||
          "Sikertelen foglalás.";
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async update(id, updateData) {
      this.loading = true;
      this.error = null;
      try {
        await service.update(id, updateData);
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
        return true;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async delete(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.delete(id);
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
        return true;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // Barber nézethez: törlés (lemondás) után saját foglalások újratöltése
    async cancelForBarber(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.delete(id);
        const response = await service.getAll();
        this.items = response.data;
        return true;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
