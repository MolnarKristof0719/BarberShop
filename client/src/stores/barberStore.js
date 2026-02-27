import { defineStore } from "pinia";
import { useSearchStore } from "./searchStore";
import service from "@/api/barberService";

class Item {
  constructor(
    id = 0,
    userId = 0,
    profilePicture = "",
    introduction = "",
    isActive = true,
  ) {
    this.id = id;
    this.userId = userId;
    this.profilePicture = profilePicture;
    this.introduction = introduction;
    this.isActive = isActive;
  }
}

export const useBarberStore = defineStore("barber", {
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
    setColumn(column) {
      this.getAllSortSearch(column);
    },

    clearItem() {
      this.item = new Item();
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
        this.error = err;
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
  },
});
