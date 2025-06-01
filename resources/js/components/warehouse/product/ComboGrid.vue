<template>
    <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">Danh sách sản phẩm combo</h2>
      <div class="flex justify-end items-center gap-2 mb-2">
        <button @click="openModal" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
          + Thêm nhanh sản phẩm
        </button>
      </div>
    <div class="relative">
      <table class="min-w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
          <tr v-for="item in comboItems" :key="item.id">
            <td class="px-3 py-2"><img :src="item.image" class="w-10 h-10 rounded object-cover" /></td>
            <td class="px-3 py-2">{{ item.product_name }}</td>
            <td class="px-3 py-2">{{ item.stock_name }}</td>
            <td class="px-3 py-2">{{ item.product_type_text }}</td>
            <td class="px-3 py-2">{{ item.sku }}</td>
            <td class="px-3 py-2">1</td>
            <td class="px-3 py-2">{{ item.quantity }}</td>
            <td class="px-3 py-2">{{ item.unit_name || '' }}</td>
            <td class="px-3 py-2">{{ item.sell_price }}</td>
            <td class="px-3 py-2">{{ item.purchase_price }}</td>
            <td class="px-3 py-2">0</td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="comboItems.length === 0">
            <td colspan="11" class="text-center py-4 text-gray-500">
              <i class="fa fa-file"></i> No data
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50">
      <div class="absolute inset-0 bg-gray-200 bg-opacity-90"></div>
      <div class="bg-white w-[90%] max-w-[1200px] min-h-[600px] rounded-xl shadow-lg z-10 p-6 relative flex flex-col">
        <h3 class="text-lg font-semibold mb-4">Thêm nhanh sản phẩm</h3>
        <CommonTable
          :columns="productColumns"
          :data="productList"
          :pagination="pagination"
          :withCheckbox="true"
          placeholder="Tìm sản phẩm theo tên, mã SKU..."
          @search="onSearch"
          @page-change="onPageChange"
          @selection-change="onSelected"
        >
        </CommonTable>
      <div class="flex justify-end gap-2 pt-6 mt-auto">
        <button class="px-4 py-1 rounded bg-red-300 text-white hover:bg-red-400" @click="closeModal">Hủy</button>
        <button class="px-4 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" @click="onSaveComboItems">
          Lưu
        </button>
      </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'

export default {
  components: { CommonTable },
  data() {
    return {
      showModal: false,
      searchKeyword: '',
      productList: [],
      comboItems: [],
      selectedProductItems: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      },
      productColumns: [
        { label: 'Hình ảnh', key: 'image', type: 'image_file' },
        { label: 'SKU', key: 'sku' },
        { label: 'Tên sản phẩm', key: 'product_name' },
        { label: 'Loại SP', key: 'product_type_text' },
        { label: 'Mã vạch', key: 'barcode' },
        { label: 'Kho', key: 'stock_name' },
        { label: 'Tồn kho', key: 'quantity' },
        { label: 'Giá bán', key: 'sell_price' },
        { label: 'Giá mua', key: 'purchase_price' }
      ]
    }
  },
  methods: {
    closeModal()
    {
      this.showModal = false
      this.searchKeyword = ''
    },
    onSelected(items) {
      this.selectedProductItems = items
    },
    onSaveComboItems() {
      const newItems = this.selectedProductItems.filter(item =>
        !this.comboItems.some(c => c.id === item.id)
      )
      this.comboItems.push(...newItems)
      this.selectedProductItems = []
      this.showModal = false
      this.productList = []
      this.pagination = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      }
    },
    async openModal() {
      this.showModal = true
      const exceptsSingle = this.comboItems
        .filter(i => i.product?.type === 'single')
        .map(i => i.id)
      const exceptsVariable = this.comboItems
        .filter(i => i.product?.type === 'variable')
        .map(i => i.id)

      await this.fetchProducts(1, exceptsSingle, exceptsVariable)
    },
    async fetchProducts(page = 1, exceptsSingle = [], exceptsVariable = []) {
      try {
        const res = await window.axios.get('/api/warehouse/product/get-init-combo', {
          params: {
            page,
            keyword: this.searchKeyword,
            limit: this.pagination.per_page,
            excepts_single: exceptsSingle.join(','),
            excepts_variable: exceptsVariable.join(',')
          }
        })
        this.productList = res.data.data || []
        Object.assign(this.pagination, res.data)
      } catch (error) {
        console.error('Lỗi khi fetch products:', error)
      }
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

<style scoped>
</style>
