<template>
  <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">{{ $t('variant_grid.title') }}</h2>

    <!-- Áp dụng toàn bộ (full hàng, không nền xám) -->
    <div class="grid grid-cols-4 gap-4 mb-4">
      <input
        :value="formatCurrency(applyToAll.purchase_price)"
        @input="onApplyAllInput($event, 'purchase_price')"
        class="px-3 py-1 border rounded w-full variant-input"
        :placeholder="$t('variant_grid.placeholder.purchase_price')+' (₫)'"
      />

      <input
        :value="formatCurrency(applyToAll.sell_price)"
        @input="onApplyAllInput($event, 'sell_price')"
        class="px-3 py-1 border rounded w-full variant-input"
        :placeholder="$t('variant_grid.placeholder.sell_price')+' (₫)'"
      />

      <input v-model.number="applyToAll.quantity" type="number" min="0"
      class="px-3 py-1 border rounded w-full variant-input" :placeholder="$t('variant_grid.placeholder.quantity')" />
      <div v-if="trashVariants.length" class="flex items-center gap-4">
        <button
          @click="applyAll"
          class="h-10 w-full bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer flex-grow"
        >
          {{ $t('variant_grid.apply_all', { total: variants.length }) }}
        </button>

        <div v-if="trashVariants.length">
          <button
            @click="handleAddRestore"
            class="h-10 min-w-[160px] bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer"
          >
            Khôi phục nháp ({{ trashVariants.length }})
          </button>
        </div>
      </div>

      <div v-else>
        <button
          @click="applyAll"
          class="h-10 w-full bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer"
        >
          {{ $t('variant_grid.apply_all', { total: variants.length }) }}
        </button>
      </div>

    </div>
    <div ref="variantScrollContainer" class="rounded border border-gray-300 overflow-y-auto max-h-[600px] min-h-[600px] custom-scroll">
       <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-blue-50">
          <tr>
            <th
              v-for="(attrName, index) in previewAttributes"
              :key="index"
              class="px-4 py-2 font-semibold text-center break-words whitespace-normal max-w-[8rem] w-[8rem]"
            >
              {{ attrName }}
            </th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.image') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.stock') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.quantity') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.purchase_price') }} (₫)</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.sell_price') }} (₫)</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.sku') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.barcode') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.is_sale') }}</th>
            <th class="px-4 py-2 font-semibold text-center">{{ $t('variant_grid.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="restoringVariant">
            <td v-for="(attrName, index) in previewAttributes" :key="'restore-' + index" class="px-4 py-2">
              <select
                v-model="restoringAttributes[index]"
                class="w-32 px-2 py-1 border rounded"
                :disabled="index > 0 && !restoringAttributes[index - 1]"
                @change="onRestoreAttrChange(index)"
              >
                <option value="">{{$t('common.please_select')}}</option>
                <option
                  v-for="opt in getDeletedAttributeOptions(index)"
                  :key="opt.valueId"
                  :value="opt.valueId"
                >
                  {{ opt.valueTitle }}
                </option>
              </select>
            </td>
            <td class="px-4 py-2 text-center">
              <span class="block w-16 h-16 mx-auto border border-gray-300 rounded bg-gray-100 opacity-50"></span>
            </td>
            <td class="px-4 py-2">
              <select v-model="restoringStock" class="w-32 px-2 py-1 border rounded">
                <option value="">{{ $t('variant_grid.choose_warehouse') }}</option>
                <option
                  v-for="stock in restoringStockOptions"
                  :key="stock"
                  :value="stock"
                >
                  {{ getStockName(stock) }}
                </option>
              </select>
            </td>
            <td colspan="6" class="px-4 py-2 text-center text-gray-400">({{$t('variant_grid.disable_field_add_msg')}})</td>
            <td class="px-4 py-2 text-center space-x-2">
              <button @click="confirmRestore" class="text-green-600 hover:text-green-800 font-bold text-xl leading-none">✔</button>
              <button @click="cancelRestore" class="text-gray-500 hover:text-gray-700 font-bold text-xl leading-none">×</button>
            </td>
          </tr>

          <tr v-for="(variant, index) in variants" :key="index" class="hover:bg-gray-50">
            <td v-for="attr in variant.attributes" :key="attr.value.id" class="px-4 py-2">{{ attr.value.title }}</td>
            <td class="px-4 py-2 text-center">
              <div class="relative w-20 h-20 mx-auto border-2 border-dashed rounded-lg overflow-hidden group">
                <input
                  type="file"
                  class="absolute inset-0 opacity-0 z-10 cursor-pointer"
                  @change="onImageChange($event, index)"
                  accept="image/*"
                />
                <img
                  v-if="variant.image"
                  :src="getImageUrl(variant.image)"
                  class="w-full h-full object-cover"
                />
                <div v-else class="flex items-center justify-center h-full text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 16l4-4 4 4m0 0l4-4 4 4M4 4h16v16H4z" />
                  </svg>
                </div>

              <button
                v-if="variant.image"
                @click="removeImage(index)"
                class="absolute cursor-pointer top-0 right-0 text-white bg-red-500 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold z-20 hidden group-hover:flex"
              ><XMarkIcon class="w-6 h-6 text-white-500" /></button>
              </div>
            </td>
            <td class="px-4 py-2">{{ getStockName(variant.stock_id) }}</td>
            <td class="px-4 py-2">
              <input v-model="variant.quantity" type="number" min="0" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input
                :value="formatCurrency(variant.purchase_price)"
                @input="onCurrencyInput($event, index, 'purchase_price')"
                class="w-32 px-2 py-1 border rounded variant-input"
              />
            </td>
            <td class="px-4 py-2">
              <input
                :value="formatCurrency(variant.sell_price)"
                @input="onCurrencyInput($event, index, 'sell_price')"
                class="w-32 px-2 py-1 border rounded variant-input"
              />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.sku" type="text" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.barcode" type="text" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2 text-center align-middle">
              <input
                type="checkbox"
                v-model="variant.is_sale"
                true-value="1"
                false-value="0"
                class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
              />
            </td>
            <td class="px-4 py-2 text-center">
              <button
                @click="$emit('delete-variant', index)"
                class="text-red-600 hover:text-red-800 font-bold text-xl leading-none"
              >×</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2'
import { XMarkIcon } from '@heroicons/vue/24/solid'

export default {
  name: 'VariantGrid',
  components: { XMarkIcon },
  props: {
    variants: Array,
    stocks: Array,
    previewAttributes: Array,
    trashVariants: Array,
    restoringVariant: Object,
  },
  data() {
    return {
      restoringAttributes: [],
      restoringStock: '',
      applyToAll: {
        purchase_price: null,
        sell_price: null,
        quantity: null
      }
    }
  },
  watch: {
    totalVariants(newVal) {
      if (newVal > 100) {
        Swal.fire({
          icon: 'error',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })
      }
    }
  },
  computed: {
    totalVariants() {
      return this.variants.length + this.trashVariants.length
    },
    isVariantLimitExceeded() {
      return this.totalVariants > 100
    },
    isRestoreReady() {
      return (
        this.restoringAttributes.length === this.previewAttributes.length &&
        !this.restoringAttributes.includes('')
      )
    },
    restoringStockOptions() {
      if (!this.isRestoreReady) return []

      const selectedKey = this.restoringAttributes
        .map((valueId, index) => {
          const attr = this.getSelectedAttributes()[index]
          return `${attr.id}:${valueId}`
        })
        .sort()
        .join('|')

      const matchedStocks = this.trashVariants
        .filter(variant => {
          const key = variant.attributes
            .map(a => `${a.attribute.id}:${a.value.id}`)
            .sort()
            .join('|')
          return key === selectedKey
        })
        .map(v => v.stock_id)

      return [...new Set(matchedStocks)]
    }
  },
  methods: {
    getSelectedAttributes() {
      const form = this.$parent.form || {}
      if (form.variant_input_mode === 'from_category') {
        return this.$parent.selectedAttributes || []
      }

      return (form.custom_attributes || []).map(attr => ({
        id: attr.id,
        title: attr.title,
        attributes: attr.values || []
      }))
    },
    formatCurrency(value) {
      if (value === null || value === undefined || value === '') return ''
      const number = Number(value)
      return isNaN(number) ? '' : number.toLocaleString('vi-VN')
    },
    onCurrencyInput(e, index, field) {
      const raw = e.target.value.replace(/[^\d]/g, '')
      const value = parseInt(raw || '0')
      this.variants[index][field] = value
    },
    onApplyAllInput(e, field) {
      const raw = e.target.value.replace(/[^\d]/g, '')
      this.applyToAll[field] = raw ? Number(raw) : ''
    },
    applyAll() {
      this.variants.forEach((variant) => {
          variant.purchase_price = Number(this.applyToAll.purchase_price) || 0
          variant.sell_price = Number(this.applyToAll.sell_price) || 0
          variant.quantity = Number(this.applyToAll.quantity) || 0
      })

      this.$nextTick(() => {
        this.$toast?.success?.('Áp dụng thành công cho tất cả biến thể')
      })
    },
    removeImage(index) {
      const variant = this.variants[index]
      if (typeof variant.image === 'string' && variant.id) {
        this.$emit('remove:variant-image', variant.id)
      }
      variant.image = null
    },
    handleAddRestore() {
      if (this.isVariantLimitExceeded) {
        Swal.fire({
          icon: 'warning',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })
        return
      }

      this.$emit('start-restore')
      this.$nextTick(() => {
        const el = this.$refs.variantScrollContainer
        if (el) el.scrollTo({ top: 0, behavior: 'smooth' })
      })
    },
    getStockName(stockId) {
      const stock = this.stocks.find(s => s.stock_id === stockId)
      return stock ? stock.name : '—'
    },
    onImageChange(e, index) {
      const file = e.target.files[0]
      if (file) {
        this.$emit('update:image', { index, file })
      }
    },
    getImageUrl(file) {
      return typeof file === 'string' ? file : URL.createObjectURL(file)
    },
    getDeletedAttributeOptions(index) {
      const attrTitle = this.previewAttributes[index]
      const attrObj = this.getSelectedAttributes().find(a => a.title === attrTitle)
      if (!attrObj) return []

      const used = []
      this.trashVariants.forEach(variant => {
        const attr = variant.attributes[index]
        if (attr?.attribute?.id === attrObj.id && !used.includes(attr.value.id)) {
          used.push(attr.value.id)
        }
      })

      if (index > 0 && this.restoringAttributes[index - 1]) {
        const prevAttr = this.previewAttributes[index - 1]
        const prevAttrObj = this.getSelectedAttributes().find(a => a.title === prevAttr)
        const prevVal = this.restoringAttributes[index - 1]

        return attrObj.attributes.filter(a => {
          return this.trashVariants.some(v =>
            v.attributes.some(attr =>
              attr.attribute.id === attrObj.id &&
              a.id === attr.value.id &&
              v.attributes.some(p => p.attribute.id === prevAttrObj.id && p.value.id == prevVal)
            )
          )
        }).map(a => ({ valueId: a.id, valueTitle: a.title }))
      }

      return attrObj.attributes.filter(a => used.includes(a.id)).map(a => ({ valueId: a.id, valueTitle: a.title }))
    },
    onRestoreAttrChange(index) {
      for (let i = index + 1; i < this.restoringAttributes.length; i++) {
        this.restoringAttributes[i] = ''
      }
      this.restoringStock = ''
    },
    cancelRestore() {
      this.$emit('cancel-restore')
      this.restoringAttributes = []
      this.restoringStock = ''
    },
    confirmRestore() {
      if (this.restoringAttributes.includes('') || !this.restoringStock) {
        Swal.fire({
          icon: 'warning',
          title: 'Thiếu thông tin',
          text: 'Vui lòng chọn đầy đủ các giá trị!',
          confirmButtonText: 'Đã hiểu'
        })
        return
      }
      const attributes = this.restoringAttributes.map((valueId, index) => {
        const attr = this.getSelectedAttributes()[index]
        const value = attr.attributes.find(a => a.id == valueId)
        return {
          attribute: {
            id: attr.id,
            title: attr.title
          },
          value: {
            id: value.id,
            title: value.title,
            variant_id: attr.id
          }
        }
      })
      this.$emit('confirm-restore', {
        stock_id: this.restoringStock,
        attributes,
        quantity: 0,
        sell_price: 0,
        purchase_price: 0,
        sku: '',
        barcode: '',
        image: null,
        is_sale: 1
      })
      this.restoringAttributes = []
      this.restoringStock = ''
    }
  }
}
</script>

<style scoped>
table th:nth-child(n+1):nth-child(-n+5),
table td:nth-child(n+1):nth-child(-n+5) {
  text-align: center;
  vertical-align: middle;
  white-space: normal;
  word-break: break-word;
  line-height: 1.4;
}
.variant-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
  line-height: 1.25rem;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.variant-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

input[type="text"],
input[type="number"],
select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
  line-height: 1.25rem;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
  outline: none;
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

select:disabled {
  background-color: #f9fafb;
  cursor: not-allowed;
  opacity: 0.7;
}

.custom-scroll::-webkit-scrollbar {
  width: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f0f0f0;
  border-radius: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background-color: #a0aec0;
  border-radius: 4px;
  transition: background 0.3s ease;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
  background-color: #718096;
}
.group:hover .group-hover\\:flex {
  display: flex !important;
}
</style>