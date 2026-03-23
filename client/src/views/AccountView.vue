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

        <div class="col-12" v-if="isBarber">
          <div class="info-box">
            <small class="label">Szabadnap rögzítése</small>
            <div class="d-flex flex-wrap gap-2 mt-2">
              <button
                class="btn btn-dark btn-sm"
                type="button"
                @click="openDayoffRequest"
                :disabled="barberLoading || dayoffUploading || !barberRecordId"
              >
                {{ dayoffUploading ? "Rögzítés..." : "Új szabadnap rögzítése" }}
              </button>
            </div>
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
            <div class="mt-3">
              <button class="btn btn-outline-dark btn-sm" type="button" @click="openPasswordChange">
                Jelszó módosítása
              </button>
            </div>
          </div>
        </div>

        <div class="col-12" v-if="isBarber">
          <div class="info-box">
            <small class="label">Profilkép csere</small>
            <p class="mb-0 mt-2 helper-text">
              Kattints az avatárra és válassz egy képet. A rendszer a régi képet automatikusan törli.
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
                {{ referenceUploading ? "Feltöltés..." : "Új referenciakép feltöltése" }}
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
                  alt="Referenciakép"
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

    <PasswordChangeForm
      ref="passwordForm"
      :title="passwordTitle"
      @yesEventForm="yesEventPasswordHandler"
    />

    <div v-if="showDayoffModal" class="dayoff-modal-backdrop" @click="closeDayoffRequest">
      <div class="dayoff-modal" @click.stop>
        <div class="d-flex align-items-center justify-content-between gap-2">
          <h3 class="mb-0">Szabadnap rögzítése</h3>
          <button class="btn-close" type="button" @click="closeDayoffRequest"></button>
        </div>

        <div class="mt-3">
          <label class="form-label mb-1" for="dayoffDate">Válassz egy napot</label>
          <input
            id="dayoffDate"
            v-model="dayoffDate"
            type="date"
            class="form-control"
            :min="todayDateString()"
          />
          <p v-if="dayoffError" class="text-danger mt-2 mb-0">{{ dayoffError }}</p>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button
            class="btn btn-dark btn-sm"
            type="button"
            @click="submitDayoffRequest"
            :disabled="dayoffUploading || !dayoffDate"
          >
            {{ dayoffUploading ? "Küldés..." : "Szabadnap rögzítése" }}
          </button>
          
        </div>

        <div class="mt-3">
          <small class="label">Már rögzített szabadnapjaid</small>
          <p v-if="dayoffLoading" class="mb-0 mt-2 helper-text">Betöltés...</p>
          <p v-else-if="!dayoffItems.length" class="mb-0 mt-2 helper-text">Még nincs rögzített szabadnap.</p>
          <div v-else class="dayoff-list mt-2">
            <div v-for="dayoff in dayoffItems" :key="dayoff.id" class="dayoff-item">
              <span>{{ formatHungarianDate(dayoff.offDay) }}</span>
              <button
                class="btn btn-outline-danger btn-sm"
                type="button"
                @click="deleteDayoff(dayoff.id)"
                :disabled="dayoffDeletingId === dayoff.id || dayoffUploading"
              >
                {{ dayoffDeletingId === dayoff.id ? "Törlés..." : "Törlés" }}
              </button>
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
import barberOffDayService from "@/api/barberOffDayService";
import referencePictureService from "@/api/referencePictureService";
import { resolveMediaUrl } from "@/utils/media";
import PasswordChangeForm from "@/components/Forms/PasswordChangeForm.vue";

export default {
  name: "AccountView",
  components: {
    PasswordChangeForm,
  },
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
      showDayoffModal: false,
      dayoffDate: "",
      dayoffItems: [],
      dayoffLoading: false,
      dayoffUploading: false,
      dayoffDeletingId: null,
      dayoffError: "",
      savingProfile: false,
      profileErrors: {},
      editForm: {
        name: "",
        email: "",
        phoneNumber: "",
      },
      passwordTitle: "Jelszó módosítása",
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
    ...mapActions(useUserLoginLogoutStore, ["updateMe", "updatePasswordMe"]),
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
    openPasswordChange() {
      this.passwordTitle = "Jelszó módosítása";
      this.$refs.passwordForm.show();
    },
    async yesEventPasswordHandler({ item, done }) {
      try {
        await this.updatePasswordMe(item);
        const toastStore = useToastStore();
        toastStore.messages.push("Jelszó sikeresen módosítva.");
        toastStore.show("Success");
        done(true);
      } catch (error) {
        if (error?.response?.status === 422) {
          this.$refs.passwordForm.setServerErrors(error.response.data.errors);
        }
        done(false);
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
      this.showDayoffModal = false;
      this.dayoffDate = "";
      this.dayoffItems = [];
      this.dayoffLoading = false;
      this.dayoffUploading = false;
      this.dayoffDeletingId = null;
      this.dayoffError = "";
    },
    normalizeOffDay(value) {
      const raw = String(value || "");
      return raw.includes("T") ? raw.slice(0, 10) : raw;
    },
    formatHungarianDate(value) {
      const day = this.normalizeOffDay(value);
      if (!day) return "-";
      const [year, month, date] = day.split("-").map(Number);
      if (!year || !month || !date) return day;
      return new Date(year, month - 1, date).toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    },
    todayDateString() {
      const date = new Date();
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    async loadDayoffItems() {
      this.dayoffLoading = true;
      try {
        const response = await barberOffDayService.getAll();
        const rows = Array.isArray(response?.data) ? response.data : [];
        this.dayoffItems = rows
          .map((row) => ({
            ...row,
            offDay: this.normalizeOffDay(row.offDay),
          }))
          .sort((a, b) => String(a.offDay).localeCompare(String(b.offDay)));
      } catch {
        this.dayoffItems = [];
      } finally {
        this.dayoffLoading = false;
      }
    },
    async openDayoffRequest() {
      if (!this.isBarber || this.barberLoading || !this.barberRecordId) return;
      this.showDayoffModal = true;
      this.dayoffError = "";
      this.dayoffDate = this.todayDateString();
      await this.loadDayoffItems();
    },
    closeDayoffRequest() {
      this.showDayoffModal = false;
      this.dayoffError = "";
    },
    async submitDayoffRequest() {
      if (!this.dayoffDate) return;

      this.dayoffUploading = true;
      this.dayoffError = "";
      try {
        await barberOffDayService.create({ offDay: this.dayoffDate });
        await this.loadDayoffItems();

        const toastStore = useToastStore();
        toastStore.messages.push("Szabadnap sikeresen rögzítve.");
        toastStore.show("Success");
      } catch (error) {
        this.dayoffError =
          error?.response?.data?.message || "A szabadnap rögzítése nem sikerült.";
      } finally {
        this.dayoffUploading = false;
      }
    },
    async deleteDayoff(id) {
      if (!id) return;

      this.dayoffDeletingId = id;
      try {
        await barberOffDayService.delete(id);
        this.dayoffItems = this.dayoffItems.filter((row) => row.id !== id);

        const toastStore = useToastStore();
        toastStore.messages.push("Szabadnap törölve.");
        toastStore.show("Success");
      } catch {
      } finally {
        this.dayoffDeletingId = null;
      }
    },
    async loadReferencePictures() {
      if (!this.barberRecordId) {
        this.referencePictures = [];
        return;
      }

      this.referenceLoading = true;
      try {
        const response = await referencePictureService.getAll(this.barberRecordId);
        if (Array.isArray(response?.data)) {
          this.referencePictures = response.data;
        } else if (Array.isArray(response)) {
          this.referencePictures = response;
        } else {
          this.referencePictures = [];
        }
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
        this.barberProfilePicture =
          response?.data?.profilePicture ||
          response?.profilePicture ||
          this.barberProfilePicture;

        const toastStore = useToastStore();
        toastStore.messages.push("Profilkép sikeresen frissítve.");
        toastStore.show("Success");
      } catch (error) {
        const toastStore = useToastStore();
        const errors = error?.response?.data?.errors || {};
        const firstError =
          errors?.profilePicture?.[0] ||
          error?.response?.data?.message ||
          "A profilkép feltöltése nem sikerült.";
        toastStore.messages.push(firstError);
        toastStore.show("Error");
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
        await referencePictureService.create(file, this.barberRecordId);
        await this.loadReferencePictures();

        const toastStore = useToastStore();
        toastStore.messages.push("Referenciakép sikeresen feltöltve.");
        toastStore.show("Success");
      } catch (error) {
        const toastStore = useToastStore();
        const errors = error?.response?.data?.errors || {};
        const firstError =
          errors?.picture?.[0] ||
          errors?.barberId?.[0] ||
          error?.response?.data?.message ||
          "A referenciakép feltöltése nem sikerült.";
        toastStore.messages.push(firstError);
        toastStore.show("Error");
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
        toastStore.messages.push("Referenciakép törölve.");
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

.dayoff-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.dayoff-modal {
  width: min(520px, 100%);
  max-height: min(86vh, 760px);
  border-radius: 14px;
  background: #fff;
  border: 1px solid #d8dee5;
  padding: 16px;
  overflow-y: auto;
}

.dayoff-list {
  display: grid;
  gap: 8px;
}

.dayoff-item {
  border: 1px solid #e1e6eb;
  border-radius: 10px;
  background: #fff;
  padding: 8px 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

@media (max-width: 576px) {
  .reference-grid {
    grid-template-columns: 1fr;
  }
}
</style>
