<template>
  <div class="app-shell container-fluid my-container mt-3">
    <Header class="app-header" />
    <Menu class="app-nav mt-2" />
    <Breadcrumb class="app-breadcrumb mt-2" />

    <main class="app-main mt-3">
      <RouterView class="app-page" />
    </main>

    <Footer class="app-footer mt-3" :content="footerContent" />
    <ToastContanier />
  </div>
</template>

<script>
import Menu from "./components/Layout/Menu.vue";
import Header from "./components/Layout/Header.vue";
import Footer from "./components/Layout/Footer.vue";
import Breadcrumb from "./components/Layout/Breadcrumb.vue";
import ToastContanier from "./components/Message/ToastContanier.vue";

export default {
  components: {
    Menu,
    Header,
    Footer,
    Breadcrumb,
    ToastContanier,
  },
  data() {
    return {
      footerContent: "Ez egy masik lablec szoveg",
    };
  },
};
</script>

<style>
.app-shell {
  --app-bg: #f0f2f5;
  --app-card: #ffffff;
  --app-dark: #111111;
  --app-accent: #c5a059;
  background: linear-gradient(180deg, #f7f8fa 0%, var(--app-bg) 100%);
  border-radius: 16px;
  padding: 12px;
}

.app-header,
.app-nav,
.app-breadcrumb,
.app-main,
.app-footer {
  background: var(--app-card);
  border: 1px solid #e9ecef;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(17, 17, 17, 0.05);
}

.app-main {
  min-height: calc(100vh - 280px);
  padding: 14px;
}

.app-page {
  height: 100%;
}
</style>
