<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Danh sách sản phẩm</h1>

    <CommonTable
      :columns="columns"
      :data="products"
      :pagination="pagination"
      @search="onSearch"
      @page-change="onPageChange"
      :placeholder="'🔍 Tìm kiếm sản phẩm...'"
    >
      <template #buttons>
        <div class="flex gap-2 justify-end">
          <button
            class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
            @click="$router.push('/warehouse/product/create/variable')"
          >
            + Thêm sản phẩm
          </button>
          <button
            class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
            @click="$router.push('/warehouse/product/create/combo')"
          >
            + Thêm sản phẩm Combo
          </button>
        </div>
      </template>
      <template #actions="{ item }">
        <div class="relative" @click.stop>
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
import CommonTable from '@/components/common/TableList.vue'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'

export default {
  name: 'ListProduct',
  components: { CommonTable },
  data() {
    return {
      products: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      },
      searchKeyword: '',
      dropdownId: null,
      columns: [
        { label: 'Tên sản phẩm', key: 'product_name' },
        { label: 'Ảnh', key: 'image', type: 'image_file' },
        { label: 'SKU', key: 'sku' },
        { label: 'Kho', key: 'stock_name' },
        { label: 'Loại', key: 'product_type_text' },
        { label: 'Giá nhập', key: 'purchase_price' },
        { label: 'Giá bán', key: 'sell_price' },
        { label: 'Tồn kho', key: 'quantity' },
        { label: 'Đơn vị', key: 'unit_name' },
        { label: 'Trạng thái', key: 'status_text' },
      ]
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

    this.fetchProducts(this.pagination.current_page)
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
      // this.$route.push(`/warehouse/product/edit/${item.id}/${item.type}`)
    },
    onEdit(item) {
      this.$router.push(`/warehouse/product/edit/${item.product_id}/${item.product_type}`)
    },
    onDelete(item) {
      if (confirm(`Xoá sản phẩm ${item.product_name}?`)) {
        alert('Đã xóa!')
      }
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
    fetchProducts(page = 1) {
      const params = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }

      this.updateUrlQuery(page)

      window.axios.get('/api/warehouse/product/list', { params }).then(res => {
        this.products = res.data.data.data
        Object.assign(this.pagination, res.data.data)
      })
    },
    onSearch(keyword) {
      this.searchKeyword = keyword
      this.fetchProducts(1)
    },
    onPageChange(page) {
      this.fetchProducts(page)
    }
  }
}
</script>
