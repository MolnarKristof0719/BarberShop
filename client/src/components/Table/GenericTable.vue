<template>
  <div class="table-responsive my-table-container">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark sticky-top my-table-head">
        <tr class="align-middle">
          <th class="actions-column">Műveletek</th>
          <template v-for="col in columns" :key="col.key">
            <th
              v-if="col.debug >= 1"
              class="my-pointer"
              :class="{ 'my-debug': col.debug == 1 }"
              @click="$emit('sort', col.key)"
            >
              <div class="d-flex align-items-center justify-content-center text-nowrap">
                <span>{{ col.label }}</span>
                <span :class="{ invisible: sortColumn !== col.key }" class="ms-1">
                  {{ sortDirection === "asc" ? "▲" : "▼" }}
                </span>
              </div>
            </th>
          </template>
        </tr>
      </thead>

      <tbody class="table-group-divider">
        <tr
          v-for="item in items"
          :key="item.id"
          class="my-table-row"
          :class="{ 'table-primary': selectedId === item.id }"
          @click="onClickRow(item.id)"
        >
          <td class="actions-column">
            <ButtonsCrud
              :id="item.id"
              :cButtonVisible="cButtonVisible"
              :uButtonVisible="uButtonVisible"
              :dButtonVisible="dButtonVisible"
              :pButtonVisible="pButtonVisible"
              @delete="$emit('delete', $event)"
              @update="$emit('update', $event)"
              @create="$emit('create', $event)"
              @passwordChange="$emit('passwordChange', $event)"
            />
          </td>

          <template v-for="col in columns" :key="col.key">
            <td v-if="col.debug >= 1" :class="{ 'my-debug': col.debug == 1 }">
              {{ item[col.key] }}
            </td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import ButtonsCrud from "./ButtonsCrud.vue";

export default {
  name: "GenericTable",
  components: {
    ButtonsCrud,
  },
  props: {
    items: { type: Array, required: true },
    columns: { type: Array, required: true },
    useCollectionStore: { type: Function, required: true },
    cButtonVisible: { type: Boolean, default: true },
    uButtonVisible: { type: Boolean, default: true },
    dButtonVisible: { type: Boolean, default: true },
    pButtonVisible: { type: Boolean, default: false },
  },
  data() {
    return {
      selectedId: null,
      store: null,
    };
  },
  created() {
    if (this.useCollectionStore) {
      this.store = this.useCollectionStore();
    }
  },
  computed: {
    sortColumn() {
      return this.store ? this.store.sortColumn : "";
    },
    sortDirection() {
      return this.store ? this.store.sortDirection : "asc";
    },
  },
  methods: {
    onClickRow(id) {
      this.selectedId = id;
    },
  },
};
</script>

<style scoped>
.my-table-container {
  max-height: calc(100vh - 360px);
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  background: #ffffff;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.my-table-head {
  z-index: 10;
  top: 0;
}

.table {
  width: 100%;
  min-width: 780px;
}

.table th,
.table td {
  padding: 0.85rem 0.9rem;
}

.actions-column {
  white-space: nowrap;
  width: 1%;
  text-align: center;
}

.my-table-row {
  cursor: pointer;
  transition: transform 0.14s ease, box-shadow 0.14s ease;
}

.my-table-row:hover {
  transform: translateY(-1px);
  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
}

@media (max-width: 767px) {
  .table {
    min-width: 600px;
  }

  .table th,
  .table td {
    padding: 0.65rem 0.7rem;
  }
}

@media (max-width: 575.98px) {
  .table {
    min-width: 520px;
  }
}
</style>
