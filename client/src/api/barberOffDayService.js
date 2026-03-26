import apiClient from "./axiosClient";

const route = "/barber_off_days";

export default {
  async getAll() {
    return await apiClient.get(route);
  },

  async getAllSortSearch(column, direction, search) {
    const searchValue = search ?? "all";
    return await apiClient.get(
      `${route}sortsearch/${column}/${direction}/${searchValue}`,
    );
  },

  async create(data) {
    return await apiClient.post(route, data);
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
