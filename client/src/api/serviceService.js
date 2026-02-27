import apiClient from "./axiosClient";
const route = "/services";

export default {
  async getAllSortSearch(column = "id", direction = "asc", search = "") {
    const currentRoute = `/servicessortsearch/${column}/${direction}/${search}`;
    return await apiClient.get(currentRoute);
  },

  async getAll() {
    return await apiClient.get(`${route}`);
  },

  async getById(id) {
    const url = `${route}/${id}`;
    return await apiClient.get(url);
  },

  async create(data) {
    delete data.id;
    return await apiClient.post(`${route}`, data);
  },

  async update(id, data) {
    delete data.id;
    return await apiClient.patch(`${route}/${id}`, data);
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
