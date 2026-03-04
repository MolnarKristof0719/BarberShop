<template>
  <section class="account-page">
    <div class="account-card">
      <button class="avatar-large avatar-button" type="button" @click="onAvatarClick"
        :disabled="!isBarber || barberLoading || !barberRecordId"
        :title="isBarber ? 'Profilkép csere' : 'Csak barbernek módosítható'">
        <img v-if="avatarImageSrc" :src="avatarImageSrc" alt="Profilkep" class="avatar-image" />
        <i v-else class="bi bi-person"></i>
      </button>

      <input ref="profileFileInput" type="file" class="d-none" accept="image/png,image/jpeg,image/jpg,image/webp"
        @change="onProfilePictureSelected" />

      <h1 class="mb-1">{{ item?.name || "-" }}</h1>
      <p class="text-muted mb-4">Bejelentkezett felhasználó adatai</p>

      <div class="row gy-3">
        <div class="col-12">
          <div class="info-box">
            <small class="label">Név</small>
            <p class="value mb-0">{{ item?.name || "-" }}</p>
          </div>
        </div>
        <div class="col-12">
          <div class="info-box">
            <small class="label">Email</small>
            <p class="value mb-0">{{ item?.email || "-" }}</p>
          </div>
        </div>
        <div class="col-12">
          <div class="info-box">
            <small class="label">Telefon</small>
            <p class="value mb-0">{{ item?.phoneNumber || "-" }}</p>
          </div>
        </div>
        <div class="col-12">
          <div class="info-box">
            <small class="label">Szerepkör</small>
            <p class="value mb-0">{{ roleLabel }}</p>
          </div>
        </div>

        <div class="col-12">
          <div class="info-box">
            <small class="label">Fiók adatok módosítása</small>
            <form class="row g-2 mt-1" @submit.prevent="saveProfile">
              <div class="col-12">
                <label class="form-label mb-1" for="accountName">Név</label>
                <input id="accountName" v-model="editForm.name" type="text" class="form-control"
                  :class="{ 'is-invalid': profileErrors.name }" @input="clearProfileError('name')" />
                <div class="invalid-feedback" v-if="profileErrors.name">
                  {{ profileErrors.name[0] }}
                </div>
              </div>

              <div class="col-12">
                <label class="form-label mb-1" for="accountEmail">Email</label>
                <input id="accountEmail" v-model="editForm.email" type="email" class="form-control"
                  :class="{ 'is-invalid': profileErrors.email }" @input="clearProfileError('email')" />
                <div class="invalid-feedback" v-if="profileErrors.email">
                  {{ profileErrors.email[0] }}
                </div>
              </div>

              <div class="col-12">
                <label class="form-label mb-1" for="accountPhone">Telefon (opcionális)</label>
                <input id="accountPhone" v-model="editForm.phoneNumber" type="text" class="form-control"
                  :class="{ 'is-invalid': profileErrors.phoneNumber }" @input="clearProfileError('phoneNumber')" />
                <div class="invalid-feedback" v-if="profileErrors.phoneNumber">
                  {{ profileErrors.phoneNumber[0] }}
                </div>
              </div>

              <div class="col-12 mt-3">
                <button class="btn btn-dark" type="submit" :disabled="savingProfile || loading">
                  {{ savingProfile ? "Mentés..." : "Mentés" }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-12" v-if="isBarber">
          <div class="info-box">
            <small class="label">Profilkép csere</small>
            <p class="mb-0 mt-2 helper-text">
              Kattints az avatárra és valássz egy képet. A rendszer a régi képet automatikusan törli.
            </p>
            <p class="mb-0 mt-2 selected-file" v-if="selectedFileName">
              Kiválasztott: {{ selectedFileName }}
            </p>
          </div>
        </div>

        <div class="col-12" v-if="isBarber">
          <div class="info-box">
            <small class="label">Referenciaképek kezelése</small>

            <div class="d-flex flex-wrap gap-2 mt-2">
              <button
                class="btn btn-dark btn-sm"
                type="button"
                @click="openReferenceUpload"
                :disabled="barberLoading || referenceUploading || !barberRecordId"
              >
                {{ referenceUploading ? "Feltöltés..." : "Új referencia kép feltöltése" }}
              </button>
            </div>

            <input
              ref="referenceFileInput"
              type="file"
              class="d-none"
              accept="image/png,image/jpeg,image/jpg,image/webp"
              @change="onReferencePictureSelected"
            />

            <p v-if="referenceLoading" class="mb-0 mt-3 helper-text">
              Referenciaképek betöltése...
            </p>
            <p v-else-if="!referencePictures.length" class="mb-0 mt-3 helper-text">
              Még nincs feltöltött referencia képed.
            </p>
            <div v-else class="reference-grid mt-3">
              <div
                v-for="picture in referencePictures"
                :key="picture.id"
                class="reference-card"
              >
                <img
                  class="reference-image"
                  :src="resolveImage(picture.picture)"
                  alt="Referencia kep"
                />
                <button
                  class="btn btn-outline-danger btn-sm w-100"
                  type="button"
                  @click="deleteReferencePicture(picture.id)"
                  :disabled="referenceDeletingId === picture.id || referenceUploading"
                >
                  {{ referenceDeletingId === picture.id ? "Törlés..." : "Törlés" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";
import barberService from "@/api/barberService";
import referencePictureService from "@/api/referencePictureService";
import { resolveMediaUrl } from "@/utils/media";

export default {
  name: "AccountView",
  data() {
    return {
      barberRecordId: null,
      barberProfilePicture: "",
      barberLoading: false,
      selectedFileName: "",
      referencePictures: [],
      referenceLoading: false,
      referenceUploading: false,
      referenceDeletingId: null,
      savingProfile: false,
      profileErrors: {},
      editForm: {
        name: "",
        email: "",
        phoneNumber: "",
      },
    };
  },
  watch: {
    item: {
      async handler() {
        this.syncEditForm();
        await this.loadBarberProfile();
      },
      immediate: true,
    },
    role: {
      async handler() {
        await this.loadBarberProfile();
      },
      immediate: true,
    },
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "role", "loading"]),
    isBarber() {
      return this.role === 2;
    },
    avatarImageSrc() {
      if (!this.isBarber || !this.barberProfilePicture) return null;
      return resolveMediaUrl(this.barberProfilePicture);
    },
    roleLabel() {
      const labels = {
        1: "Admin",
        2: "Barber",
        3: "User",
      };
      return labels[this.role] || "-";
    },
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["updateMe"]),
    resolveImage(path) {
      return resolveMediaUrl(path);
    },
    syncEditForm() {
      this.editForm.name = this.item?.name || "";
      this.editForm.email = this.item?.email || "";
      this.editForm.phoneNumber = this.item?.phoneNumber || "";
      this.profileErrors = {};
    },
    clearProfileError(field) {
      if (!this.profileErrors[field]) return;
      this.profileErrors = {
        ...this.profileErrors,
        [field]: null,
      };
    },
    async saveProfile() {
      if (!this.item) return;

      const payload = {
        name: (this.editForm.name || "").trim(),
        email: (this.editForm.email || "").trim(),
      };

      const phoneNumber = (this.editForm.phoneNumber || "").trim();
      if (phoneNumber) payload.phoneNumber = phoneNumber;

      const noChanges =
        payload.name === (this.item.name || "") &&
        payload.email === (this.item.email || "") &&
        (payload.phoneNumber || "") === (this.item.phoneNumber || "");
      if (noChanges) return;

      this.savingProfile = true;
      this.profileErrors = {};
      try {
        await this.updateMe(payload);
        const toastStore = useToastStore();
        toastStore.messages.push("Profil adatok sikeresen frissitve.");
        toastStore.show("Success");
        this.syncEditForm();
      } catch (error) {
        if (error?.response?.status === 422) {
          this.profileErrors = error?.response?.data?.errors || {};
        }
      } finally {
        this.savingProfile = false;
      }
    },
    resetBarberState() {
      this.barberRecordId = null;
      this.barberProfilePicture = "";
      this.selectedFileName = "";
      this.referencePictures = [];
      this.referenceLoading = false;
      this.referenceUploading = false;
      this.referenceDeletingId = null;
    },
    async loadReferencePictures() {
      if (!this.barberRecordId) {
        this.referencePictures = [];
        return;
      }

      this.referenceLoading = true;
      try {
        const response = await referencePictureService.getAll(this.barberRecordId);
        this.referencePictures = Array.isArray(response?.data) ? response.data : [];
      } catch {
        this.referencePictures = [];
      } finally {
        this.referenceLoading = false;
      }
    },
    async loadBarberProfile() {
      if (!this.isBarber || !this.item?.id) {
        this.resetBarberState();
        return;
      }

      this.barberLoading = true;
      try {
        const response = await barberService.getAll();
        const barberItems = response?.data || [];
        const currentBarber = barberItems.find((barber) => Number(barber.userId) === Number(this.item.id));

        if (!currentBarber) {
          this.resetBarberState();
          return;
        }

        this.barberRecordId = currentBarber.id;
        this.barberProfilePicture = currentBarber.profilePicture || "";
        await this.loadReferencePictures();
      } catch (error) {
        this.resetBarberState();
      } finally {
        this.barberLoading = false;
      }
    },
    onAvatarClick() {
      if (!this.isBarber || this.barberLoading || !this.barberRecordId) return;
      this.$refs.profileFileInput?.click();
    },
    async onProfilePictureSelected(event) {
      const file = event?.target?.files?.[0];
      if (!file || !this.barberRecordId) return;

      this.selectedFileName = file.name;
      this.barberLoading = true;

      try {
        const response = await barberService.uploadProfilePicture(this.barberRecordId, file);
        this.barberProfilePicture = response?.data?.profilePicture || this.barberProfilePicture;

        const toastStore = useToastStore();
        toastStore.messages.push("Profilkep sikeresen frissitve.");
        toastStore.show("Success");
      } catch (error) {
      } finally {
        this.barberLoading = false;
        if (this.$refs.profileFileInput) {
          this.$refs.profileFileInput.value = "";
        }
      }
    },
    openReferenceUpload() {
      if (!this.isBarber || this.barberLoading || !this.barberRecordId) return;
      this.$refs.referenceFileInput?.click();
    },
    async onReferencePictureSelected(event) {
      const file = event?.target?.files?.[0];
      if (!file || !this.barberRecordId) return;

      this.referenceUploading = true;
      try {
        await referencePictureService.create(file);
        await this.loadReferencePictures();

        const toastStore = useToastStore();
        toastStore.messages.push("Referenciakep sikeresen feltoltve.");
        toastStore.show("Success");
      } catch (error) {
      } finally {
        this.referenceUploading = false;
        if (this.$refs.referenceFileInput) {
          this.$refs.referenceFileInput.value = "";
        }
      }
    },
    async deleteReferencePicture(id) {
      if (!id) return;

      this.referenceDeletingId = id;
      try {
        await referencePictureService.delete(id);
        this.referencePictures = this.referencePictures.filter((picture) => picture.id !== id);

        const toastStore = useToastStore();
        toastStore.messages.push("Referenciakep torolve.");
        toastStore.show("Success");
      } catch (error) {
      } finally {
        this.referenceDeletingId = null;
      }
    },
  },
};
</script>

<style scoped>
.account-page {
  min-height: 100%;
  display: flex;
  justify-content: center;
  padding: 24px 10px;
}

.account-card {
  width: 100%;
  max-width: 560px;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(17, 17, 17, 0.08);
  padding: 22px;
}

.avatar-large {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  background: #111111;
  color: #c5a059;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  margin-bottom: 16px;
  overflow: hidden;
}

.avatar-button {
  border: 2px solid #111111;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.avatar-button:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.15);
}

.avatar-button:disabled {
  opacity: 0.8;
  cursor: default;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.info-box {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 10px 12px;
  background: #f8f9fa;
}

.label {
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.value {
  color: #111111;
  font-weight: 600;
}

.helper-text {
  color: #495057;
}

.selected-file {
  color: #111111;
  font-weight: 600;
}

.form-label {
  color: #343a40;
}

.reference-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.reference-card {
  border: 1px solid #e9ecef;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  padding: 8px;
  display: grid;
  gap: 8px;
}

.reference-image {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: 8px;
}

@media (max-width: 576px) {
  .reference-grid {
    grid-template-columns: 1fr;
  }
}
</style>
