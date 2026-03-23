<template>
  <div class="menu-wrap">
    <nav class="navbar navbar-expand-lg menu-bar">
      <div class="container-fluid px-3 px-md-4">
        <RouterLink class="brand-mark" to="/" aria-label="Fooldal">
          <span class="logo-box"><img src="/logo.png" alt="logó" class="logo-img" /></span>
        </RouterLink>

        <button class="navbar-toggler menu-toggler" type="button" data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
          <ul class="navbar-nav align-items-lg-center gap-lg-2 mb-2 mb-lg-0">
            <li class="nav-item">
              <RouterLink class="nav-link menu-link" to="/">Főoldal</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link menu-link" to="/about">Rólunk</RouterLink>
            </li>

            <li class="nav-item">
              <RouterLink class="book-btn" to="/appointment">Book Now</RouterLink>
            </li>

            <!-- <li class="nav-item" v-if="hasMenuAccess('/service')">
              <RouterLink class="nav-link menu-link" to="/service">Szolgáltatások</RouterLink>
            </li> -->
            <!-- <li class="nav-item" v-if="hasMenuAccess('/barber')">
              <RouterLink class="nav-link menu-link" to="/barber">Barberek</RouterLink>
            </li> -->
          </ul>

          <div class="menu-meta d-flex flex-column flex-lg-row align-items-lg-center ms-lg-4 gap-2 gap-lg-3">
            <div class="menu-contact d-flex align-items-center gap-3">
            </div>

            <form class="d-flex menu-search" role="search" v-if="showAdminSearch">
              <input id="search" class="form-control" type="search" placeholder="Search" aria-label="Search"
                v-model="searchWordInput" />
              <label for="search" class="form-label m-0">
                <i @click="onClickSearchButton" class="bi bi-search fs-5 my-pointer"></i>
              </label>
            </form>

            <div class="nav-item" v-if="!isLoggedIn">
              <RouterLink class="btn menu-btn" to="/login">Bejelentkezés</RouterLink>
            </div>

            <ul class="navbar-nav mb-0">
              <li class="nav-item dropdown" v-if="isLoggedIn">
                <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="avatar-circle" title="Fiok"><i class="bi bi-person"></i></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end account-menu">
                  <li>
                    <RouterLink class="dropdown-item" to="/account">Fiók</RouterLink>
                  </li>
                  <template v-if="hasMenuAccess('/usersmeappointment')">
                    <li>
                      <RouterLink class="dropdown-item" to="/usersmeappointment">Foglalásaim</RouterLink>
                    </li>
                  </template>
                   <template v-if="hasMenuAccess('/usersmeappointmentbarber')">
                    <li>
                      <RouterLink class="dropdown-item" to="/usersmeappointmentbarber">Foglalások</RouterLink>
                    </li>
                  </template>
                  <template v-if="hasMenuAccess('/admin')">
                    <li>
                      <hr class="dropdown-divider" />
                    </li>
                    <li>
                      <RouterLink class="dropdown-item" to="/admin/users">Admin - Userek</RouterLink>
                    </li>
                    <li>
                      <RouterLink class="dropdown-item" to="/admin/barbers">Admin - Barberek</RouterLink>
                    </li>
                    <li>
                      <RouterLink class="dropdown-item" to="/admin/services">Admin - Szolgáltatások</RouterLink>
                    </li>
                    <li>
                      <RouterLink class="dropdown-item" to="/admin/reviews">Admin - Vélemények</RouterLink>
                    </li>
                  </template>
                  <li>
                    <hr class="dropdown-divider" />
                  </li>
                  <li>
                    <button class="dropdown-item text-danger" type="button" @click="onClickLogout">
                      Kijelentkezés
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
.book-btn {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  text-decoration: none;
  border: 1px solid #111111;
  border-radius: 0;
  padding: 10px 24px;
  font-size: 0.95rem;
}

.menu-wrap {
  border-radius: 12px;
  overflow: visible;
}

.menu-bar {
  background: #ffffff;
  min-height: 108px;
  border-radius: 12px;
  padding: 0.75rem 0;
}

.brand-mark {
  width: 78px;
  height: 78px;
  background: #111111;
  color: #f6f6f6;
  border: 2px solid #f6f6f6;
  box-shadow: 0 0 0 1px #111111;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  letter-spacing: 0.18em;
  font-weight: 600;
  transition: transform 0.2s ease;
}

.brand-mark:hover {
  transform: translateY(-1px);
}

.menu-link {
  color: #1a1a1a !important;
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.02em;
  padding: 0.35rem 0.7rem !important;
  border-bottom: 2px solid transparent;
}

.menu-link:hover {
  color: #000000 !important;
}

.menu-link.router-link-exact-active {
  color: #000000 !important;
  border-bottom-color: #111111;
}

.menu-phone {
  color: #111111;
  font-size: 0.92rem;
  white-space: nowrap;
}

.menu-icon {
  color: #111111;
  font-size: 0.95rem;
}

.menu-search {
  gap: 0.35rem;
}

.menu-search .form-control {
  min-width: 165px;
  border-radius: 0;
}

.menu-btn {
  background: #111111;
  color: #ffffff;
  border-radius: 0;
  border: 1px solid #111111;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

.menu-btn:hover {
  background: #000000;
  color: #ffffff;
}

.account-menu {
  min-width: 210px;
}

.avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #111111;
  color: #111111;
}

.menu-toggler {
  border-color: #111111;
}

.menu-toggler .navbar-toggler-icon {
  filter: invert(1);
}

.navbar {
  position: relative;
  z-index: 1060 !important;
}

.dropdown-menu {
  z-index: 1060 !important;
}

@media (max-width: 991.98px) {
  .menu-bar {
    min-height: unset;
  }

  .brand-mark {
    width: 62px;
    height: 62px;
    font-size: 1rem;
  }

  .menu-meta {
    margin-top: 0.8rem;
  }
}
</style>
