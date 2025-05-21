<template>
  <div>
    <h2 class="title">Đăng nhập</h2>
    <form @submit.prevent="login" class="login-form">
      <input v-model="email" type="email" placeholder="Email" class="input" />
      <input v-model="password" type="password" placeholder="Password" class="input" />
      <button type="submit" class="btn">Login</button>
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
        await window.axios.post('/api/auth/login', {
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

<style scoped>
.title {
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
  color: #333;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.input {
  padding: 12px 16px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 16px;
  transition: 0.3s;
}

.input:focus {
  outline: none;
  border-color: #4b79a1;
  box-shadow: 0 0 0 3px rgba(75, 121, 161, 0.2);
}

.btn {
  padding: 12px;
  background: #4b79a1;
  color: white;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn:hover {
  background: #283e51;
}
</style>
