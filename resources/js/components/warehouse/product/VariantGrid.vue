<template>
  <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">{{ $t('variant_grid.title') }}</h2>

    <div v-if="trashVariants.length" class="flex justify-end items-center gap-2 mb-2">
      <button @click="handleAddRestore" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $t('variant_grid.add_restore') }}
      </button>
      <span class="text-sm text-gray-500">({{ trashVariants.length }})</span>
    </div>

    <div ref="variantScrollContainer" class="rounded border border-gray-300 overflow-y-auto max-h-[600px] min-h-[600px] custom-scroll">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100 sticky top-0 z-20">
          <tr>
            <th v-for="(attrName, index) in previewAttributes" :key="index" class="px-4 py-2 text-left font-semibold">
              {{ attrName }}
            </th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.image') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.stock') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.quantity') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.purchase_price') }}</th>
            <th class="px-4 py-2 font-semibold">{{ $t('variant_grid.sell_price') }}</th>
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
                <option value="">Chọn</option>
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
                <option value="">Chọn kho</option>
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
            <td class="px-4 py-2 text-center align-middle">
              <label class="cursor-pointer relative block w-16 h-16 mx-auto border border-gray-300 rounded overflow-hidden hover:shadow">
                <input
                  type="file"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                  @change="onImageChange($event, index)"
                  accept="image/*"
                />
                <img v-if="variant.image" :src="getImageUrl(variant.image)" class="w-full h-full object-cover" />
                <img v-else src="https://static.thenounproject.com/png/1077596-200.png" class="w-full h-full object-contain opacity-40" />
              </label>
            </td>
            <td class="px-4 py-2">{{ getStockName(variant.stock_id) }}</td>
            <td class="px-4 py-2">
              <input v-model="variant.quantity" type="number" min="0" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.purchase_price" type="number" min="0" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.sell_price" type="number" min="0" class="w-32 px-2 py-1 border rounded" />
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

export default {
  name: 'VariantGrid',
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
      restoringStock: ''
    }
  },
  computed: {
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
          const attr = this.$parent.selectedAttributes[index]
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
    handleAddRestore() {
      this.$emit('start-restore')

      this.$nextTick(() => {
        const el = this.$refs.variantScrollContainer
        if (el) {
          el.scrollTo({ top: 0, behavior: 'smooth' })
        }
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
      const attrObj = this.$parent.selectedAttributes.find(a => a.title === attrTitle)
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
        const prevAttrObj = this.$parent.selectedAttributes.find(a => a.title === prevAttr)
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
        const attr = this.$parent.selectedAttributes[index]
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
table th,
table td {
  white-space: nowrap;
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

</style>