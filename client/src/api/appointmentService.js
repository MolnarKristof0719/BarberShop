import apiClient from "./axiosClient";

const route = "/appointments";

export default {
  async getAllSortSearch(column = "id", direction = "asc", search = "") {
    const currentRoute = `/appointmentssortsearch/${column}/${direction}/${search}`;
    return await apiClient.get(currentRoute);
  },

  async getAll() {
    return await apiClient.get(`${route}`);
  },

  async getById(id) {
    return await apiClient.get(`${route}/${id}`);
  },

  async create(data) {
    delete data.id;
    return await apiClient.post(`${route}`, data);
  },

  async getAvailability(params) {
    return await apiClient.get(`${route}/availability`, { params });
  },

  async getEarliestOptions(params) {
    return await apiClient.get(`${route}/earliest-options`, { params });
  },

  async update(id, data) {
    delete data.id;
    return await apiClient.patch(`${route}/${id}`, data);
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
