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
import CommonTable from '../common/TableList.vue'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'

export default {
  name: 'ListCustomer',
  components: { CommonTable },
  data() {
    return {
      customers: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 15
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
    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 15
    }

    this.fetchCustomers(this.pagination.current_page)
  },
  methods: {
    updateUrlQuery(page = 1) {
      const queryObj = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }
      const encoded = encodeQuery(queryObj)

      this.$router.replace({
        path: this.$route.path,
        query: { query: encoded }
      })
    },
    fetchCustomers(page = 1) {
      const params = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }

      this.updateUrlQuery(page)

      window.axios.get('/api/customer/list', { params }).then(res => {
        this.customers = res.data.data

        const {
          current_page,
          last_page,
          from,
          to,
          per_page,
          total
        } = res.data

        Object.assign(this.pagination, {
          current_page,
          last_page,
          from,
          to,
          per_page,
          total
        })
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
