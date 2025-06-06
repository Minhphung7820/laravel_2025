<template>
  <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">{{ $t('combo_grid.title') }}</h2>
    <div class="flex justify-end items-center gap-2 mb-2">
      <button @click="openModal" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $t('combo_grid.add_quick') }}
      </button>
    </div>
    <div class="rounded border border-gray-300 overflow-y-auto max-h-[600px] min-h-[600px] custom-scroll">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100 sticky top-0 z-20">
          <tr>
            <th class="px-3 py-2">{{ $t('combo_grid.image') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.product_name') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.stock') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.type') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.sku') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.quantity') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.available_stock') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.unit') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.sell_price') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.purchase_price') }}</th>
            <th class="px-3 py-2">{{ $t('combo_grid.combo_price') }}</th>
            <th class="px-3 py-2 text-center">{{ $t('combo_grid.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in comboItems" :key="item.parent_id" class="hover:bg-gray-50">
            <td class="px-3 py-2"><img :src="item.image" class="w-10 h-10 rounded object-cover" /></td>
            <td class="px-3 py-2">
              <span>
                {{ item.product_name }}
                <template v-if="item.product?.type === 'variable'">
                  - {{ item.attribute_first?.title || '' }}
                  <template v-if="item.attribute_second">
                    - {{ item.attribute_second.title }}
                  </template>
                </template>
              </span>
            </td>
            <td class="px-3 py-2">
              <select
                class="border px-2 py-1 rounded"
                v-model="item.stock_id"
                @change="onSelectStock(item)"
              >
                <template v-if="item.product?.type === 'variable'">
                  <option v-for="variant in item.related_variants || []" :key="variant.id" :value="variant.stock_id">
                    {{ variant.stock_name || $t('common.unnamed') }}
                  </option>
                </template>
                <template v-else>
                  <option v-for="s in item.product?.stock_data || []" :key="s.stock_id" :value="s.stock_id">
                    {{ s.stock?.name || $t('common.unnamed') }}
                  </option>
                </template>
              </select>
            </td>
            <td class="px-3 py-2">{{ $t('product_type.'+ item.product_type) }}</td>
            <td class="px-3 py-2">{{ item.sku }}</td>
            <td class="px-3 py-2">
              <input
                type="number"
                min="1"
                class="border px-2 py-1 rounded w-20 text-center"
                v-model.number="item.quantity_combo"
                :placeholder="$t('combo_grid.quantity')"
              />
            </td>
            <td class="px-3 py-2">{{ item.quantity }}</td>
            <td class="px-3 py-2">{{ item.unit_name || '' }}</td>
            <td class="px-3 py-2">{{ item.sell_price }}</td>
            <td class="px-3 py-2">{{ item.purchase_price }}</td>
            <td class="px-3 py-2">
              <input
                type="number"
                min="0"
                class="border px-2 py-1 rounded w-24 text-right"
                v-model.number="item.sell_price_combo"
                :placeholder="$t('combo_grid.combo_price')"
              />
            </td>
            <td class="px-3 py-2 text-center">
              <button @click="removeComboItem(index)" class="text-red-600 hover:text-red-800 font-bold text-xl leading-none">×</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50">
      <div class="absolute inset-0 bg-gray-200 bg-opacity-90"></div>
      <div class="bg-white w-[90%] max-w-[1200px] min-h-[600px] rounded-xl shadow-lg z-10 p-6 relative flex flex-col">
        <h3 class="text-lg font-semibold mb-4">{{ $t('combo_grid.add_quick') }}</h3>
        <CommonTable
          :columns="productColumns"
          :data="productList"
          :pagination="pagination"
          :withCheckbox="true"
          :placeholder="$t('combo_grid.search_placeholder')"
          @search="onSearch"
          @page-change="onPageChange"
          @selection-change="onSelected"
        />
        <div class="flex justify-end gap-2 pt-6 mt-auto">
          <button class="px-4 py-1 rounded bg-red-300 text-white hover:bg-red-400" @click="closeModal">{{ $t('combo_grid.cancel') }}</button>
          <button class="px-4 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" @click="onSaveComboItems">
            {{ $t('combo_grid.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import Swal from 'sweetalert2'

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
        { label: this.$t('combo_grid.image'), key: 'image', type: 'image_file' },
        { label: this.$t('combo_grid.sku'), key: 'sku' },
        { label: this.$t('combo_grid.product_name'), key: 'product_name' },
        { label: this.$t('combo_grid.type'), key: 'product_type' , withLang : true , keyLang : 'product_type'  },
        { label: this.$t('combo_grid.barcode'), key: 'barcode' },
        { label: this.$t('combo_grid.stock'), key: 'stock_name' },
        { label: this.$t('combo_grid.available_stock'), key: 'quantity' },
        { label: this.$t('combo_grid.sell_price'), key: 'sell_price' },
        { label: this.$t('combo_grid.purchase_price'), key: 'purchase_price' }
      ],
      exceptsSingle: [],
      exceptsVariable: []
    }
  },
  mounted() {
    this.refreshSelectedComboStock()
  },
  watch: {
    comboItems: {
      handler(items) {
        items.forEach(item => {
          if (item.stock_id) {
            this.onSelectStock(item)
          }
        })
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    refreshSelectedComboStock() {
      this.comboItems.forEach(item => {
        if (item.stock_id) {
          this.onSelectStock(item)
        }
      })
    },
    onSelectStock(item) {
      if (item.product?.type === 'variable') {
        const selected = (item.related_variants || []).find(
          v => v.stock_id === item.stock_id
        )
        if (selected) {
          item.sku = selected.sku || ''
          item.sell_price = selected.sell_price || 0
          item.purchase_price = selected.purchase_price || 0
          item.quantity = selected.quantity || 0
          item.stock_name = selected.stock?.name || ''
          item.attr1_title = selected.attr1_title || ''
          item.attr2_title = selected.attr2_title || ''
        }
      } else {
        const selected = (item.product?.stock_data || []).find(
          s => s.stock_id === item.stock_id
        )
        if (selected) {
          item.sell_price = selected.sell_price || 0
          item.purchase_price = selected.purchase_price || 0
          item.quantity = selected.quantity || 0
          item.stock_name = selected.stock?.name || ''
        }
      }
    },
    closeModal(){
      this.showModal = false
      this.searchKeyword = ''
      this.productList = []
      this.exceptsSingle = []
      this.exceptsVariable = []
      this.pagination = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      }
    },
    onSelected(items) {
      this.selectedProductItems = items
    },
    onSaveComboItems() {
      // Xử lý parse related_variants nếu cần
      this.selectedProductItems.forEach(item => {
        if (typeof item.related_variants === 'string') {
          try {
            item.related_variants = JSON.parse(item.related_variants)
          } catch (e) {
            console.warn('Lỗi parse related_variants:', e)
            item.related_variants = []
          }
        }
        item.parent_id = item.id
        item.id = null
        item.sell_price_combo = item.sell_price_combo ?? 0
        item.quantity_combo = item.quantity_combo ?? 1
      })

      // Lọc các item chưa tồn tại
      const newItems = this.selectedProductItems.filter(item =>
        !this.comboItems.some(c => c.parent_id === item.parent_id)
      )

      // Kiểm tra giới hạn
      const totalAfterAdd = this.comboItems.length + newItems.length
      if (totalAfterAdd > 30) {
        Swal.fire({
          icon: 'error',
          title: this.$t('combo_grid.limit_exceeded_title') || 'Vượt quá giới hạn!',
          text: this.$t('combo_grid.limit_exceeded_msg') || `Bạn chỉ được thêm tối đa 30 sản phẩm vào combo.`,
          confirmButtonText: 'OK'
        })
        return
      }

      // Nếu hợp lệ thì push
      this.comboItems.unshift(...newItems)

      // Reset trạng thái
      this.selectedProductItems = []
      this.showModal = false
      this.productList = []
      this.searchKeyword = ''
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

      this.exceptsSingle = this.comboItems
        .filter(i => i.product?.type === 'single')
        .map(i => i.parent_id)

      this.exceptsVariable = this.comboItems
        .filter(i => i.product?.type === 'variable')
        .map(i => i.parent_id)

      await this.fetchProducts(1)
    },
    async fetchProducts(page = 1) {
      try {
        const res = await window.axios.get('/api/warehouse/product/get-init-combo', {
          params: {
            page,
            keyword: this.searchKeyword,
            limit: this.pagination.per_page,
            excepts_single: this.exceptsSingle.join(','),
            excepts_variable: this.exceptsVariable.join(',')
          }
        })
        this.productList = res.data.data.data || []
        Object.assign(this.pagination, res.data.data)
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
    },
    removeComboItem(index) {
      this.comboItems.splice(index, 1)
    },
  }
}
</script>

<style scoped>
</style>
