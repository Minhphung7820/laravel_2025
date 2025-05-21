<template>
  <div>
    <h2>Đăng nhập</h2>
    <form @submit.prevent="login">
      <input v-model="email" type="email" placeholder="Email" />
      <input v-model="password" type="password" placeholder="Password" />
      <button type="submit">Login</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: ''
    }
  },
  methods: {
    async login() {
      try {
        await window.axios.get('/sanctum/csrf-cookie')
        await window.axios.post('/api/login', {
          email: this.email,
          password: this.password
        })
        await this.$store.dispatch('auth/fetchUser')
        this.$router.push('/')
      } catch (err) {
        alert('Login thất bại. Vui lòng kiểm tra lại tài khoản.')
      }
    }
  }
}
</script>
