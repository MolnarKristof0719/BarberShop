import { defineStore } from "pinia";
import service from "@/api/usersmeappointmentService";

export const useUsersmeAppointmentStore = defineStore("usersmeAppointment", {
  state: () => ({
    items: [],
    loading: false,
    error: null,
  }),

  actions: {
    async getAll() {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAll();
        this.items = response?.data || [];
        return this.items;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async cancel(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.cancel(id);
        this.items = this.items.map((item) =>
          item.id === id
            ? { ...item, status: "cancelled", cancelledBy: "customer" }
            : item,
        );
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
