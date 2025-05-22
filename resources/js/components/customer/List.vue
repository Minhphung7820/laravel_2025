<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Danh sách khách hàng</h1>
    <CommonTable
      :columns="columns"
      :data="customers"
      :pagination="pagination"
      @search="onSearch"
      @page-change="onPageChange"
    />
  </div>
</template>

<script>
import CommonTable from '../common/TableList.vue';

export default {
  name: 'ListCustomer',
  components: { CommonTable },
  data() {
    return {
      customers: [],
      pagination: {
        current_page: 1,
        last_page: 1
      },
      columns: [
        { label: 'Tên khách hàng', key: 'name' },
        { label: 'Email', key: 'email' },
        { label: 'SĐT', key: 'phone' },
        { label: 'Địa chỉ', key: 'address' }
      ],
      searchKeyword: ''
    }
  },
  mounted() {
    this.fetchCustomers()
  },
  methods: {
    fetchCustomers(page = 1) {
      const params = {
        page,
        keyword: this.searchKeyword,
        limit: 15
      }

      window.axios.get('/api/customer/list', { params }).then(res => {
        this.customers = res.data.data
        this.pagination.current_page = res.data.current_page
        this.pagination.last_page = res.data.last_page
        this.pagination.from = res.data.from
        this.pagination.to = res.data.to
        this.pagination.per_page = res.data.per_page
        this.pagination.total = res.data.total
      })
    },
    onSearch(keyword) {
      this.searchKeyword = keyword
      this.fetchCustomers(1)
    },
    onPageChange(page) {
      this.fetchCustomers(page)
    }
  }
}
</script>
