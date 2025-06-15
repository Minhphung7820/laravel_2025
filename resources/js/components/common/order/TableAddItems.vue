<template>
  <div class="mt-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold mb-2">Sản phẩm</h2>
      <button @click="openModal" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
        +Thêm nhiều sản phẩm
      </button>
    </div>

    <div class="rounded border border-gray-300 overflow-y-auto max-h-[600px] min-h-[600px] custom-scroll">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-blue-50">
          <tr>
            <th class="px-4 py-2 text-left">SKU</th>
            <th class="px-4 py-2 text-left">Tên sản phẩm</th>
            <th class="px-4 py-2 text-left">Loại SP</th>
            <th class="px-4 py-2 text-left">Kho</th>
            <th class="px-4 py-2 text-left">Đơn vị</th>
            <th class="px-4 py-2 text-right">Giá bán lẻ</th>
            <th class="px-4 py-2 text-right">Giá bán</th>
            <th class="px-4 py-2 text-center">Số lượng</th>
            <th class="px-4 py-2 text-right">Thành tiền</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr v-for="(item, index) in form.items" :key="index">
            <td class="px-4 py-2">
              <img :src="item.image" class="w-10 h-10 object-cover rounded" />
            </td>
            <td class="px-4 py-2">{{ item.sku }}</td>
            <td class="px-4 py-2">{{ item.product_name }}</td>
            <td class="px-4 py-2">{{ item.product_type }}</td>

            <!-- Kho: select từ item.stock -->
            <td class="px-4 py-2">
              <select
                v-if="item.stock && item.stock.length"
                v-model="item.selected_stock_id"
                @change="updateStockData(item)"
                class="border px-2 py-1 rounded"
              >
                <option
                  v-for="stockItem in item.stock"
                  :key="stockItem.stock_id"
                  :value="stockItem.stock_id"
                >
                  {{ stockItem.stock_name }}
                </option>
              </select>
            </td>

            <td class="px-4 py-2 text-right">{{ formatCurrency(item.sell_price) }}</td>

            <!-- Giá bán chỉnh được -->
            <td class="px-4 py-2 text-right">
              <input
                type="number"
                v-model.number="item.sell_price_main"
                class="w-24 px-2 py-1 border rounded text-right"
              />
            </td>

            <td class="px-4 py-2 text-center">
              <input
                type="number"
                v-model.number="item.quantity"
                min="1"
                class="w-16 px-2 py-1 border rounded text-center"
              />
            </td>

            <td class="px-4 py-2 text-right">
              {{ formatCurrency(item.sell_price_main * item.quantity) }}
            </td>
          </tr>
          <tr v-if="!form.items.length">
            <td colspan="9" class="text-center py-6 text-gray-500">
              <div class="flex justify-center items-center gap-2">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z" />
                </svg>
                Không có sản phẩm
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Nền mờ -->
      <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="showModal = false"></div>

      <!-- Modal chính -->
      <div class="relative z-10 bg-white rounded-xl shadow-xl max-w-6xl w-full mx-auto p-6">
        <div class="flex justify-between items-center mb-4">
          <label class="flex items-center gap-2">
            <span class="text-sm text-gray-700">Lấy tất cả</span>
            <input type="checkbox" v-model="getAll" @change="handleSwitchChange" class="switch-checkbox">
          </label>
        </div>

        <CommonTable
          ref="table"
          :title="'+ Thêm nhanh sản phẩm'"
          :columns="columns"
          :data="items"
          :pagination="pagination"
          :isLoading="isLoading"
          :withCheckbox="true"
          @search="onSearch"
          @page-change="onPageChange"
        />

        <div class="mt-4 flex justify-end gap-2">
          <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" @click="showModal = false">Hủy</button>
          <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="addSelected">Lưu</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import { formatCurrency } from '@/utils/currency'
export default {
  components: { CommonTable},
  props: {
    form: Object,
    transaction_type: String
  },
  data() {
    return {
      searchKeyword : '',
      showModal: false,
      items: [],
      pagination: { current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0 },
      isLoading : true,
      getAll: false,
      columns: [
        { key: 'image', label: 'HÌNH ẢNH', type: 'image_file' },
        { key: 'sku', label: 'SKU' },
        { key: 'product_name', label: 'TÊN SẢN PHẨM' },
        { key: 'product_type', label: 'LOẠI SP' },
        { key: 'stock_name', label: 'CHI NHÁNH' },
        { key: 'quantity', label: 'TỒN KHO' },
        { key: 'sell_price', label: 'GIÁ BÁN', isMoney: true },
        { key: 'purchase_price', label: 'GIÁ MUA', isMoney: true }
      ]
    }
  },
  methods: {
    formatCurrency,
    addSelected() {
      const selected = this.$refs.table?.selectedItems || []
      if (!selected.length) return
      const newItems = selected.map(item => {
        let stock = []
        try {
          stock = JSON.parse(item.stocks || '[]')
        } catch (e) {
          console.warn('Lỗi parse stocks:', e)
        }

        return {
          image: item.image,
          sku: item.sku,
          product_name: item.product_name,
          product_type: item.product_type,
          stock_name: item.stock_name,
          unit_name: item.unit_name,
          purchase_price: item.purchase_price,
          sell_price: item.sell_price,
          purchase_price_main: item.purchase_price_main,
          sell_price_main: item.sell_price_main,
          quantity: 1,
          total: item.sell_price_main * 1,
          stock: stock,
          stock_id: item.stock_id,
          selected_stock_id: item.stock_id
        }
      })

      this.$emit('add-items', newItems)
      this.showModal = false
    },
    updateStockData(item) {
      const selected = item.stock.find(s => s.stock_id === item.selected_stock_id)
      if (!selected) return

      item.sell_price_main = selected.sell_price_main
      item.sell_price = selected.sell_price
      item.purchase_price_main = selected.purchase_price_main
      item.image = selected.image
      item.stock_name = selected.stock_name
    },
    async onSearch(keyword) {
      this.searchKeyword = keyword
      await this.fetchItems(1)
    },
    async onPageChange(page) {
      await this.fetchItems(page)
    },
    async handleSwitchChange() {
      await this.fetchItems()
    },
    async openModal() {
      this.showModal = true
      await this.fetchItems()
    },
    async fetchItems(page = 1) {
      this.isLoading = true
      this.items = []
      const params = {
        page,
        get_all: this.getAll ? 1 : 0,
        keyword : this.searchKeyword
      }

      if (!this.getAll) {
        params.transaction_type = this.transaction_type
        params.customer_id = this.form.customer_id
      }
      try {
        const res = await window.axios.get('/api/warehouse/product/get-init-order', { params })
        this.items = res.data.data
        this.pagination = {
          current_page: res.data.current_page,
          last_page: res.data.last_page,
          per_page: res.data.per_page,
          from: res.data.from,
          to: res.data.to,
          total: res.data.total
        }
      } catch (e) {
        console.error(e)
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>

<style scoped>
.switch-checkbox {
  width: 40px;
  height: 20px;
  position: relative;
  appearance: none;
  background: #ccc;
  border-radius: 20px;
  outline: none;
  cursor: pointer;
  transition: background 0.3s;
}
.switch-checkbox:checked {
  background: #4caf50;
}
.switch-checkbox:before {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  top: 1px;
  left: 1px;
  background: white;
  transition: transform 0.3s;
}
.switch-checkbox:checked:before {
  transform: translateX(20px);
}
</style>
