<template>
  <div>
    <nav class="nav">
      <router-link to="/" class="nav-link">Trang chủ</router-link>
      <router-link to="/about" class="nav-link">Về chúng tôi</router-link>
    </nav>

    <div class="content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {
  name: 'App',
  components: {},
}
</script>

<style scoped>
.nav {
  background: #333;
  padding: 10px;
  display: flex;
  gap: 10px;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: bold;
  padding: 6px 12px;
  border-radius: 5px;
  transition: background 0.3s;
}

.nav-link:hover {
  background: #555;
}

.content {
  padding: 20px;
}
</style>
