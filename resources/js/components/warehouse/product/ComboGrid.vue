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
        <thead class="bg-blue-50">
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
            <th class="px-3 py-2">{{ $t('combo_grid.combo_price') }} (đ)</th>
            <th class="px-3 py-2 text-center">{{ $t('combo_grid.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in comboItems" :key="item.parent_id" class="hover:bg-gray-50">
            <td class="px-3 py-2"><img :src="item.image" class="w-10 h-10 rounded object-cover" /></td>
            <td class="px-3 py-2">
              <span class="truncate-cell" :title="item.product_name">
                {{ item.product_name }}
              </span>
              <!-- <template v-if="item.product?.type === 'variable'">
                <span class="truncate-cell" :title="item.attribute_first?.title">
                  - {{ item.attribute_first?.title || '' }}
                </span>
                <template v-if="item.attribute_second">
                  <span class="truncate-cell" :title="item.attribute_second.title">
                    - {{ item.attribute_second.title }}
                  </span>
                </template>
              </template> -->
            </td>
            <td class="px-3 py-2">
              <select
                class="border border-gray-300 rounded-md px-2 py-1 w-full focus:outline-none focus:ring-1 focus:ring-blue-500"
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
            <td class="px-3 py-2">
              <span
                class="text-xs font-semibold px-2 py-1 rounded"
                :class="{
                  'bg-blue-100 text-blue-800': item.product_type === 'single',
                  'bg-yellow-100 text-yellow-800': item.product_type === 'variable'
                }"
              >
                {{ $t('product_type.' + item.product_type) }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span class="truncate-cell" :title="item.sku">{{ item.sku }}</span>
            </td>
            <td class="px-3 py-2">
              <input
                type="number"
                min="1"
                class="border border-gray-300 rounded-md w-20 text-center px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
                v-model.number="item.quantity_combo"
                :placeholder="$t('combo_grid.quantity')"
              />
            </td>
            <td class="px-3 py-2 text-center font-medium">{{ item.quantity }}</td>
            <td class="px-3 py-2">
              <span class="truncate-cell" :title="item.unit_name">{{ item.unit_name }}</span>
            </td>
            <td class="px-3 py-2 text-center font-medium">{{ formatCurrency(item.sell_price, $i18n.locale) }}</td>
            <td class="px-3 py-2 text-center font-medium">{{ formatCurrency(item.purchase_price, $i18n.locale) }}</td>
            <td class="px-3 py-2">
              <input
                type="text"
                :value="formatCurrencyInput(item.sell_price_combo)"
                @input="onCurrencyComboInput($event, index)"
                class="border border-gray-300 rounded-md w-24 text-center px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
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
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay nền mờ -->
      <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="closeModal"></div>

      <!-- Nội dung modal -->
      <div
        class="relative z-10 bg-white w-[90%] max-w-[1200px] rounded-xl shadow-lg p-6"
        @click.stop
      >
        <CommonTable
          :title="$t('combo_grid.add_quick')"
          :columns="productColumns"
          :data="productList"
          :pagination="pagination"
          :withCheckbox="true"
          :placeholder="$t('product_list.search_placeholder')"
          @search="onSearch"
          @page-change="onPageChange"
          @selection-change="onSelected"
          :isLoading="isLoading"
        />

        <div class="flex justify-end gap-2 pt-6 mt-auto">
          <button class="px-4 py-1 rounded bg-red-300 text-white hover:bg-red-400" @click="closeModal">
            {{ $t('combo_grid.cancel') }}
          </button>
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
import { formatCurrency } from '@/utils/currency'

export default {
  components: { CommonTable },
  data() {
    return {
      isLoading : true,
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
        { label: this.$t('combo_grid.type'), key: 'product_type' ,withLang : true ,keyLang : 'product_type' ,classMap: {
            'combo': 'bg-purple-100 text-purple-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'single': 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'variable': 'bg-blue-100 text-blue-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        },
        { label: this.$t('combo_grid.barcode'), key: 'barcode' },
        { label: this.$t('combo_grid.stock'), key: 'stock_name' },
        { label: this.$t('combo_grid.available_stock'), key: 'quantity' },
        { label: this.$t('combo_grid.sell_price'), key: 'sell_price' , isMoney : true},
        { label: this.$t('combo_grid.purchase_price'), key: 'purchase_price' , isMoney : true }
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
     formatCurrencyInput(value) {
      const num = Number(value || 0)
      return num.toLocaleString('vi-VN')
    },
    onCurrencyComboInput(event, index) {
      const raw = event.target.value.replace(/[^\d]/g, '')
      const value = parseInt(raw || '0')
      this.comboItems[index].sell_price_combo = value
    },
    formatCurrency,
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
          item.image = selected.image || item.image
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
          item.image = selected.image || item.image
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
      const MAX_ITEMS = 30

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

      let newItems = this.selectedProductItems.filter(item =>
        !this.comboItems.some(c => c.parent_id === item.parent_id)
      )

      const availableSlots = MAX_ITEMS - this.comboItems.length

      if (availableSlots <= 0) {
        Swal.fire({
          icon: 'error',
          title: this.$t('combo_grid.limit_exceeded_title') || 'Vượt quá giới hạn!',
          text: this.$t('combo_grid.limit_exceeded_msg') || `Bạn chỉ được thêm tối đa ${MAX_ITEMS} sản phẩm vào combo.`,
          confirmButtonText: 'OK'
        })
        return
      }

      if (newItems.length > availableSlots) {
        newItems = newItems.slice(0, availableSlots)
        Swal.fire({
          icon: 'warning',
          title: this.$t('combo_grid.limit_exceeded_title') || 'Vượt quá giới hạn!',
          text: `Chỉ thêm được tối đa ${availableSlots} sản phẩm vào combo.`,
          confirmButtonText: 'OK'
        })
      }

      // Push dữ liệu hợp lệ
      this.comboItems.unshift(...newItems)

      // Reset trạng thái modal
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
      this.isLoading = true
      this.productList = []
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
      } finally {
        this.isLoading = false
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
.truncate-cell {
  max-width: 200px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  display: inline-block;
  vertical-align: middle;
}
</style>
