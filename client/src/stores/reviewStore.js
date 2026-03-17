import { defineStore } from "pinia";
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

    async delete(id) {
      this.loading = true;
      this.error = null;
      try {
        await reviewService.delete(id);
        this.items = this.items.filter((item) => item.id !== id);
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
