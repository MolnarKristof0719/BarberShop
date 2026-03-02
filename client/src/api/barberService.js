import apiClient from "./axiosClient";

const route = "/barbers";

export default {
  async getAllSortSearch(column = "id", direction = "asc", search = "") {
    const currentRoute = `/barberssortsearch/${column}/${direction}/${search}`;
    return await apiClient.get(currentRoute);
  },

  async getBarberById(barberId, column = "id", direction = "asc", search = "") {
    const currentRoute = `/barberbyid/${barberId}/${column}/${direction}/${search}`;
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

  async update(id, data) {
    delete data.id;
    return await apiClient.patch(`${route}/${id}`, data);
  },

  async uploadProfilePicture(id, file) {
    const formData = new FormData();
    formData.append("profilePicture", file);

    return await apiClient.post(`${route}/${id}/profile-picture`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
