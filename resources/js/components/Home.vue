<template>
  <div class="home">
    <h1>Chào mừng đến với trang chủ</h1>
    <p>Đây là trang Home, nơi bắt đầu của SPA Vue trong Laravel.</p>
  </div>
</template>

<script>
export default {
  created(){
     this.getApi();
  },
  methods: {
    async getApi() {
      try {
        const list = await window.axios.get('/api/user/list');
        console.log(list.data);
      } catch (error) {
        console.log(error?.response?.data?.message);
      }
    }
  }
}
</script>

<style scoped>
.home {
  padding: 20px;
  background: #f9f9f9;
  border-radius: 10px;
}

h1 {
  color: #2c3e50;
  margin-bottom: 10px;
}
</style>
