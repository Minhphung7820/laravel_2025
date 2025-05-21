<template>
  <div>
    <nav class="nav">
      <router-link to="/" class="nav-link">Trang chủ</router-link>
      <router-link to="/about" class="nav-link">Về chúng tôi</router-link>
      <button v-if="isLoggedIn" @click="handleLogout" class="nav-link logout-btn">Đăng xuất</button>
    </nav>

    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  computed: {
    ...mapGetters('auth', ['isAuthenticated']),
    isLoggedIn() {
      return this.isAuthenticated
    }
  },
  methods: {
    ...mapActions('auth', ['logout']),
    async handleLogout() {
      await this.logout()
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
.logout-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  font-weight: bold;
  color: white;
}

.logout-btn:hover {
  background: #555;
}

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

