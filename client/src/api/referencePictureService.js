import apiClient from "./axiosClient";

const route = "/reference_pictures";

export default {
  async getAll(barberId = null) {
    const params = {};
    if (barberId) {
      params.barberId = barberId;
    }
    return await apiClient.get(route, { params });
  },

  async create(file, barberId = null) {
    const formData = new FormData();
    formData.append("picture", file);
    if (barberId) {
      formData.append("barberId", barberId);
    }

    return await apiClient.post(route, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
  },

  async delete(id) {
    return await apiClient.delete(`${route}/${id}`);
  },
};
