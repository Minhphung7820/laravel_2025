<template>
<ul>
  <li v-for="(item, index) in engagements" :key="index">
     <p>Ngày : {{ item.day }}</p>
     <p>Tổng số người dùng : {{ item.total_users }}</p>
     <p>Tổng số người dùng tương tác : {{ item.engaged_users }}</p>
     <p>Tỷ lệ tương tác : {{ item.engagement_rate }}</p>
     <br>
  </li>
</ul>
</template>

<script>
export default {
  data(){
     return{
        engagements : []
     }
  },
  async created(){
    await this.getApi();
  },
  methods: {
    async getApi() {
      try {
        const response = await window.axios.get('/api/user/list');
        this.engagements = response.data.data
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
