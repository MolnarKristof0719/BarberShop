import apiClient from "./axiosClient";

const route = "/reviews";

export default {


  async getAll() {
    return await apiClient.get(`${route}`);
  },

  async getById(id) {
    return await apiClient.get(`${route}/${id}`);
  },

  async create(appointmentId, data) {
    delete data.id;
    return await apiClient.post(`/appointments/${appointmentId}/review`, data);
  },


  async update(id, data) {
    delete data.id;
    return await apiClient.patch(`${route}/${id}`, data);
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
