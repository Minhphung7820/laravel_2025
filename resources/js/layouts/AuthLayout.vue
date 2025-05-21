<template>
  <div class="auth-layout">
    <router-view />
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
}
</style>
