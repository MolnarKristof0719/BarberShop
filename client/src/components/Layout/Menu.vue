<template>
  <div>
    <nav class="navbar navbar-expand-md bg-primary" data-bs-theme="dark">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <RouterLink class="nav-link" to="/">Fooldal</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" to="/about">Rolunk</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/service')">
              <RouterLink class="nav-link" to="/service">Services</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/barber')">
              <RouterLink class="nav-link" to="/barber">Barberek</RouterLink>
            </li>

            <li class="nav-item dropdown" v-if="hasMenuAccess('/admin')">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Admin
              </a>
              <ul class="dropdown-menu">
                <li v-if="hasMenuAccess('/admin/users')">
                  <RouterLink class="dropdown-item" to="/admin/users">
                    Userek
                  </RouterLink>
                </li>
                <li v-if="hasMenuAccess('/admin/barbers')">
                  <RouterLink class="dropdown-item" to="/admin/barbers">
                    Admin Barberek
                  </RouterLink>
                </li>
                <li v-if="hasMenuAccess('/admin/services')">
                  <RouterLink class="dropdown-item" to="/admin/services">
                    Admin Services
                  </RouterLink>
                </li>

              </ul>
            </li>
          </ul>

          <div class="d-flex align-items-center gap-2 ms-auto">
            <form class="d-flex" role="search" v-if="showAdminSearch">
              <input id="search" class="form-control me-2" type="search" placeholder="Search" aria-label="Search"
                v-model="searchWordInput" />
              <label for="search" class="form-label m-0">
                <i @click="onClickSearchButton" class="bi bi-search fs-4 my-pointer"></i>
              </label>
            </form>

            <ul class="navbar-nav mb-2 mb-lg-0">
              <li class="nav-item" v-if="!isLoggedIn">
                <RouterLink class="nav-link" to="/login">Login</RouterLink>
              </li>

              <li class="nav-item dropdown" v-else>
                <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="avatar-circle" title="Fiok">
                    <i class="bi bi-person"></i>
                  </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <RouterLink class="dropdown-item" to="/account">Fiok</RouterLink>
                  </li>
                  <li>
                    <hr class="dropdown-divider" />
                  </li>
                  <li>
                    <button class="dropdown-item text-danger" type="button" @click="onClickLogout">
                      Kijelentkezes
                    </button>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  data() {
    return {
      searchWordInput: "",
    };
  },
  watch: {
    searchWordInput(value) {
      if (!value) {
        this.resetSearchWord();
      }
    },
    searchWord(value) {
      this.searchWordInput = value;
    },
    $route() {
      if (!this.showAdminSearch) {
        this.searchWordInput = "";
        this.resetSearchWord();
      }
    },
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord"]),
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "role"]),
    showAdminSearch() {
      return this.role === 1 && this.$route.path.startsWith("/admin");
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    ...mapActions(useUserLoginLogoutStore, ["logout"]),
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
    },
    hasMenuAccess(targetPath) {
      const userStore = useUserLoginLogoutStore();
      const resolved = this.$router.resolve(targetPath);
      if (!resolved || !resolved.matched.length) return false;
      return resolved.matched.every((route) => {
        const requiredRoles = route.meta?.roles;
        return userStore.canAccess(requiredRoles);
      });
    },
    async onClickLogout() {
      try {
        await this.logout();
        this.$router.push("/");
      } catch (error) {
        console.log("Kijelentkezesi hiba!");
      }
    },
  },
};
</script>

<style scoped>
.nav-link.active,
.nav-link.router-link-exact-active {
  color: #c5a059 !important;
  font-weight: bold;
  border-bottom: 2px solid #c5a059;
}

.nav-item:has(.dropdown-item.router-link-active) .nav-link.dropdown-toggle {
  color: #ffff00 !important;
  font-weight: bold;
  border-bottom: 2px solid yellow;
}

.dropdown-item.router-link-active {
  background-color: transparent !important;
  color: #ffff00 !important;
  font-weight: bold;
}

.avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #ffffff;
  color: #ffffff;
}

.navbar {
  position: relative;
  z-index: 1060 !important;
}

.dropdown-menu {
  z-index: 1060 !important;
}
</style>
