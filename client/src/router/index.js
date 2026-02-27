import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/HomeView.vue";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";

function checkIfNotLogged() {
  const storeAuth = useUserLoginLogoutStore();
  if (!storeAuth.isLoggedIn) {
    return "/login";
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
      meta: {
        title: () => "Fooldal",
        breadcrumb: "Fooldal",
      },
    },
    {
      path: "/about",
      name: "about",
      component: () => import("@/views/AboutView.vue"),
      meta: {
        title: () => "Rolunk",
        breadcrumb: "Rolunk",
      },
    },
    {
      path: "/service",
      name: "service",
      component: () => import("@/views/ServiceView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Service",
        breadcrumb: "Service",
        roles: [1, 2, 3],
      },
    },
    {
      path: "/barber",
      name: "barber",
      component: () => import("@/views/BarberView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Barber",
        breadcrumb: "Barber",
        roles: [1, 2, 3],
      },
    },
    {
      path: "/plaingsport",
      name: "plaingsport",
      component: () => import("@/views/PlayngSportView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Sportolas",
        breadcrumb: "Sportolas",
        roles: [1, 2],
      },
    },

    {
      path: "/admin",
      name: "admin",
      component: () => import("@/views/EmptyWrapperView.vue"),
      redirect: "/admin/barbers",
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Admin",
        breadcrumb: "Admin",
        roles: [1],
      },
      children: [
        {
          path: "users",
          name: "users",
          component: () => import("@/views/UsersView.vue"),
          meta: {
            title: () => "Users",
            breadcrumb: "Users",
            roles: [1],
          },
        },
        {
          path: "barbers",
          name: "adminBarbers",
          component: () => import("@/views/AdminBarbersView.vue"),
          meta: {
            title: () => "Admin Barberek",
            breadcrumb: "Admin Barberek",
            roles: [1],
          },
        },
        {
          path: "services",
          name: "adminServices",
          component: () => import("@/views/AdminServicesView.vue"),
          meta: {
            title: () => "Admin Services",
            breadcrumb: "Admin Services",
            roles: [1],
          },
        },
      ],
    },
    {
      path: "/account",
      name: "account",
      component: () => import("@/views/AccountView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Fiok",
        breadcrumb: "Fiok",
        roles: [1, 2, 3],
      },
    },
    {
      path: "/userprofil",
      redirect: "/account",
    },
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/LoginView.vue"),
      meta: {
        title: () => "Login",
        breadcrumb: "Login",
      },
    },
    {
      path: "/registration",
      name: "registration",
      component: () => import("@/views/RegistrationView.vue"),
      meta: {
        title: () => "Regisztracio",
        breadcrumb: "Regisztracio",
      },
    },
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: () => import("@/views/404.vue"),
      meta: {
        title: () => "404",
        breadcrumb: "",
      },
    },
  ],
});

router.beforeEach((to, from, next) => {
  document.title = "Iskola - " + to.meta.title(to);

  const requiredRoles = to.meta.roles;
  const userStore = useUserLoginLogoutStore();

  if (userStore.canAccess(requiredRoles)) {
    next();
  } else {
    if (!userStore.isLoggedIn) {
      next({ path: "/login" });
    } else {
      useToastStore().messages.push("Ehhez az oldalhoz nincs jogod!");
      useToastStore().show("Error");
      next("/");
    }
  }
});

export default router;
