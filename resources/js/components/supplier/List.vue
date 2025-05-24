<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Danh sách nhà cung cấp</h1>

    <CommonTable
      :columns="columns"
      :data="suppliers"
      :pagination="pagination"
      @search="onSearch"
      @page-change="onPageChange"
      :placeholder="'🔍 Tìm kiếm nhà cung cấp...'"
    >
      <template #buttons>
        <button
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
          @click="$router.push('/purchase/supplier/create')"
        >
          + Thêm nhà cung cấp
        </button>
      </template>
      <template #actions="{ item }">
        <div v-if="item && item.id" class="relative" @click.stop>
          <button
            @click="toggleDropdown(item.id)"
            class="px-2 py-1 rounded hover:bg-gray-100 focus:outline-none cursor-pointer"
          >
            ⋯
          </button>

          <div
              v-if="dropdownId === item.id"
              class="absolute right-0 mt-2 bg-white border rounded shadow z-50 whitespace-nowrap px-2 py-1 min-w-[100px]"
            >
              <ul class="text-sm text-gray-700">
                <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item)">👁 Xem</li>
                <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item)">✏️ Sửa</li>
                <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item)">🗑 Xóa</li>
              </ul>
          </div>
        </div>
      </template>
    </CommonTable>
  </div>
</template>


<script>
import CommonTable from '../common/TableList.vue'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'

export default {
  name: 'ListSupplier',
  components: { CommonTable },
  data() {
    return {
      suppliers: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      },
      columns: [
        { label: 'Tên nhà cung cấp', key: 'name' },
        { label: 'Email', key: 'email' },
        { label: 'SĐT', key: 'phone' },
        { label: 'Địa chỉ', key: 'address' }
      ],
      searchKeyword: '',
      dropdownId: null
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 10
    }

    this.fetchSuppliers(this.pagination.current_page)
  },
  beforeDestroy() {
     document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    closeDropdown() {
      this.dropdownId = null
    },
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    onView(item) {
      this.$router.push(`/purchase/supplier/${item.id}/detail`)
      this.dropdownId = null
    },
    onEdit(item) {
      this.$router.push(`/purchase/supplier/${item.id}/edit`)
      this.dropdownId = null
    },
    onDelete(item) {
      if (confirm(`Xóa nhà cung cấp: ${item.name}?`)) {
        console.log('Đã xoá:', item)
      }
      this.dropdownId = null
    },
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
    fetchSuppliers(page = 1) {
      const params = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }

      this.updateUrlQuery(page)

      window.axios.get('/api/supplier/list', { params }).then(res => {
        this.suppliers = res.data.data

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
      this.fetchSuppliers(1)
    },
    onPageChange(page) {
      this.fetchSuppliers(page)
    }
  }
}
</script>
