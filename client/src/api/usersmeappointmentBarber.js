import apiClient from "./axiosClient";

const route = "/usersme/appointments";

export default {
  getAll() {
    return apiClient.get(route);
  },

  getById(id) {
    return apiClient.get(`${route}/${id}`);
  },

  cancel(id) {
    return apiClient.delete(`${route}/${id}`);
  },
};
