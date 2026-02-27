<template>
  <div>
    <!-- oldal fejléc -->
    <!-- oldal címe -->
    <div class="d-flex align-items-center m-0 mb-2">
      <h1>{{ pageTitle }}</h1>
      <div class="d-flex align-items-center m-0 ms-2">
        <!-- homokóra -->
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-3 col-auto p-0 pe-1"
        ></i>
        
        <p class="m-0 ms-2">({{ getItemsLength }})</p>

       
      </div>
    </div>

    <!-- táblázat -->
    <GenericTable
      :items="items"
      :columns="tableColumns"
      :useCollectionStore="useCollectionStore"
      @delete="deleteHandler"
      @update="updateHandler"
      @sort="sortHandler"
      v-if="items.length > 0"
    />
    <div v-else style="width: 100px" class="m-auto">Nincs találat</div>

   
    <!-- Confirm modal -->
    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
//módosít
import { useBarberStore } from "@/stores/barberStore";
import { useSearchStore } from "@/stores/searchStore";
import GenericTable from "@/components/Table/GenericTable.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
export default {
  //módosít
  name: "BarberView",
  components: {
    GenericTable,
    ConfirmModal,
    ButtonsCrudCreate,
  },
  
  
  data() {
    return {
      //módosít
      pageTitle: "Barber",
      //módosít
      tableColumns: [
        { key: "id", label: "ID", debug: import.meta.env.VITE_DEBUG_MODE },
        { key: "userId", label: "---UserId---", debug: import.meta.env.VITE_DEBUG_MODE },
        { key: "profilePicture", label: "Profilkép", debug: 2 },
        { key: "introduction", label: "Bemutatkozás", debug: 2 },
        { key: "isActive", label: "Aktív", debug: 2 },
        
      ],
      //módosít
      useCollectionStore: useBarberStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r", //crud
      title: "",
    };
  },
  computed: {
    
    ...mapState(useBarberStore, [
      "item",
      "items",
      "loading",
      "getItemsLength",
      "sortColumn",
      "sortDirection",
    ]),
    ...mapState(useSearchStore, ["searchWord"]),
  },
  methods: {
    //módosít
    ...mapActions(useBarberStore, ["getAll"]),
    ...mapActions(useSearchStore, ["resetSearchWord"]),
    ...mapActions(useBarberStore, [
      "clearItem",
      "getById",
      "update",
      "delete",
    ]),
    deleteHandler(id) {
      this.state = "d";
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    updateHandler(id) {
      this.state = "u";
      this.title = "Adatmódosítás";
      this.getById(id);
      this.$refs.form.show();
      // console.log("update:", id);
    },
    
    sortHandler(column) {
      console.log(column);
      this.getStudentsBySchoolclassId(this.selectedSchoolclassId, column);
    },
    cancelHandler() {
      console.log("mégsem törlök");
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async confirmHandler() {
      try {
        await this.delete(this.toDeleteId, this.selectedSchoolclassId);
      } catch (error) {}
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async yesEventFormHandler({ item, done }) {
      //vagy create, vagy update
      try {
        if (this.state == "c") {
          //create
          await this.create(item, this.selectedSchoolclassId);
        } else {
          //update
          console.log("módosítás előtt");
          
          await this.update(item.id, item, this.selectedSchoolclassId);
          console.log("módsítás után");
          
        }
        //nem volt hiba
        this.state = "r";
        done(true);
      } catch (err) {
        console.log("valami hiba");
        
        //hiba volt
        //nem csukódik le az ablak
        if (err.response && err.response.status === 422) {
          // Átadjuk a formnak a konkrét hibaüzeneteket (pl. "min 2 karakter")
          this.$refs.form.setServerErrors(err.response.data.errors);
          done(false); // Nyitva tartja a modalt
        } else {
          // Minden más hiba (500, 401) esetén is értesítjük a modalt, hogy ne záródjon be
          done(false);
        }
        //átadom a hibát
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
