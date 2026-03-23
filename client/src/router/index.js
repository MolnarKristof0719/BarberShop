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
        title: () => "Főoldal",
      },
    },
    {
      path: "/about",
      name: "about",
      component: () => import("@/views/AboutView.vue"),
      meta: {
        title: () => "Rólunk",
      },
    },
    {
      path: "/service",
      name: "service",
      component: () => import("@/views/ServiceView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Service",
        roles: [1, 2, 3],
      },
    },
    {
      path: "/barber",
      name: "barber",
      component: () => import("@/views/BarberView.vue"),
      meta: {
        title: () => "Barber",
      },
    },
    {
      path: "/barber/:id",
      name: "barberDetail",
      component: () => import("@/views/BarberDetailView.vue"),
      meta: {
        title: () => "Barber",
      },
    },
    {
      path: "/appointment",
      name: "appointment",
      component: () => import("@/views/AppointmentView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Időpont",
        roles: [1, 2, 3],
      },
    },
    {
      path: "/services",
      name: "services",
      component: () => import("@/views/ServiceView.vue"),
      meta: {
        title: () => "Szolgáltatások",
      },
    },
    {
      path: "/usersmeappointment",
      name: "usersmeappointment",
      component: () => import("@/views/UsersmeAppointmentView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Foglalásaim",
        roles: [3],
      },
    },
    {
      path: "/usersmeappointmentbarber",
      name: "usersmeappointmentbarber",
      component: () => import("@/views/UsersmeAppointmentBarberView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Foglalások",
        roles: [2],
      },
    },
    {
      path: "/review",
      name: "review",
      component: () => import("@/views/ReviewView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: () => "Vélemény",
        roles: [3],
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
        roles: [1],
      },
      children: [
        {
          path: "users",
          name: "users",
          component: () => import("@/views/UsersView.vue"),
          meta: {
            title: () => "Users",
            roles: [1],
          },
        },
        {
          path: "barbers",
          name: "adminBarbers",
          component: () => import("@/views/AdminBarbersView.vue"),
          meta: {
            title: () => "Admin Barberek",
            roles: [1],
          },
        },
        {
          path: "services",
          name: "adminServices",
          component: () => import("@/views/AdminServicesView.vue"),
          meta: {
            title: () => "Admin Services",
            roles: [1],
          },
        },
        {
          path: "reviews",
          name: "adminReviews",
          component: () => import("@/views/AdminReviewsView.vue"),
          meta: {
            title: () => "Admin Reviews",
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
  document.title = "BarberShop - " + to.meta.title(to);

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
