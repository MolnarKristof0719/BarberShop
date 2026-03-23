import { defineStore } from "pinia";
import { useSearchStore } from "./searchStore";
import reviewService from "@/api/reviewService";

class Item {
  constructor(
    id = 0,
    appointmentId = 0,
    barberId = 0,
    userId = 0,
    rating = 0,
    comment = "",
  ) {
    this.id = id;
    this.appointmentId = appointmentId;
    this.barberId = barberId;
    this.userId = userId;
    this.rating = rating;
    this.comment = comment;
  }
}

export const useReviewStore = defineStore("review", {
  state: () => ({
    item: new Item(),
    items: [],
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
        const response = await reviewService.getAll();
        let list = response.data || [];
        const search = (this.searchStore.searchWord || "").toString().trim();
        if (search && search !== "all") {
          const s = search.toLowerCase();
          list = list.filter((r) => {
            const values = [
              r.id,
              r.barberId,
              r.userId,
              r.rating,
              r.comment,
            ];
            return values.some((v) =>
              (v ?? "").toString().toLowerCase().includes(s),
            );
          });
        }
        list.sort((a, b) => {
          const va = a?.[this.sortColumn];
          const vb = b?.[this.sortColumn];
          if (va == null && vb == null) return 0;
          if (va == null) return this.sortDirection === "asc" ? -1 : 1;
          if (vb == null) return this.sortDirection === "asc" ? 1 : -1;
          if (va === vb) return 0;
          return this.sortDirection === "asc"
            ? va > vb
              ? 1
              : -1
            : va < vb
              ? 1
              : -1;
        });
        this.items = list;
        return this.items;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },
    clearItem() {
      this.item = new Item();
    },

    async getAll() {
      this.loading = true;
      this.error = null;
      try {
        const response = await reviewService.getAll();
        this.items = response.data || [];
        return this.items;
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
        const response = await reviewService.getById(id);
        this.item = response.data;
        return this.item;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async create(appointmentId, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await reviewService.create(appointmentId, data);
        return response?.data;
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
        await reviewService.update(id, updateData);
        await this.getAllSortSearch(this.sortColumn, this.sortDirection);
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
        await reviewService.delete(id);
        await this.getAllSortSearch(this.sortColumn, this.sortDirection);
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
