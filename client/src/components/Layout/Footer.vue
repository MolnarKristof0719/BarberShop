<template>
  <footer class="site-footer">
    <div class="footer-row">
      <span>©2026 Black Timber</span>
    </div>
  </footer>
</template>

<script>
export default {
  name: "Footer",
};
</script>

<style scoped>
.site-footer {
  margin-top: 48px;
  padding: 20px;
  background: #ffffff;
  border-top: 1px solid #e2e2e2;
  color: #646464;
  font-family: "Source Sans 3", "Segoe UI", sans-serif;
  font-size: 0.7rem;
  text-align: center;
}

.footer-row {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
