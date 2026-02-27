import { defineStore } from "pinia";
// import { useToastStore } from "@/stores/toastStore";
import { useSearchStore } from "./searchStore";
import service from "@/api/barberService";

// const toast = useToastStore();

//változtatás
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
    clearItem() {
      this.item = new Item();
    },

    async getAll() {
      //   const toast = useToastStore();
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAll();
        // this.searchStore.reset();
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // READ - Egy adat lekérése
    async getById(id) {
      this.loading = true;
      this.error = null;
      //   const toast = useToastStore();
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

    

    // 3. UPDATE - Módosítás (Helyi frissítéssel, újraolvasás nélkül)
    async update(id, updateData) {
      this.loading = true;
      this.error = null;
      try {
        const updatedItem = await service.update(id, updateData);
        // const response = await service.getAll();
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
        // toast.messages.push(`Sikeresen módosítva`);
        // toast.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },

    // 4. DELETE - Törlés
    async delete(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.delete(id);
        //const response = await service.getAll();
        const response = await service.getAllSortSearch(
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord,
        );
        this.items = response.data;
        // toast.messages.push(`Sikeresen törölve`);
        // toast.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
  },
});
