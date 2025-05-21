<template>
  <div class="auth-layout">
    <div class="login-card">
      <router-view />
    </div>
  </div>
</template>

<script>
export default { name: 'AuthLayout' }
</script>

<style scoped>
.auth-layout {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
}

.login-card {
  background: white;
  padding: 40px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 400px;
}
</style>
