<template>
  <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">Lưới biến thể sản phẩm</h2>

    <div v-if="trashVariants.length" class="flex justify-end mb-2">
      <button @click="$emit('start-restore')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
        + Khôi phục dòng
      </button>
    </div>

    <div class="overflow-auto rounded border border-gray-300">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th v-for="(attrName, index) in previewAttributes" :key="index" class="px-4 py-2 text-left font-semibold">
              {{ attrName }}
            </th>
            <th class="px-4 py-2 font-semibold">Ảnh</th>
            <th class="px-4 py-2 font-semibold">Kho</th>
            <th class="px-4 py-2 font-semibold">Số lượng</th>
            <th class="px-4 py-2 font-semibold">Giá mua</th>
            <th class="px-4 py-2 font-semibold">Giá bán</th>
            <th class="px-4 py-2 font-semibold">SKU</th>
            <th class="px-4 py-2 font-semibold">Barcode</th>
            <th class="px-4 py-2 font-semibold">Mở bán</th>
            <th class="px-4 py-2 font-semibold text-center">Xóa</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="restoringVariant">
            <td v-for="(attrName, index) in previewAttributes" :key="'restore-' + index" class="px-4 py-2">
              <select v-model="restoringAttributes[index]" class="w-32 px-2 py-1 border rounded">
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
                  v-for="stock in trashVariants.map(v => v.stock_id).filter((v, i, a) => a.indexOf(v) === i)"
                  :key="stock"
                  :value="stock"
                >{{ getStockName(stock) }}</option>
              </select>
            </td>
            <td colspan="6" class="px-4 py-2 text-center text-gray-400">(Các trường khác bị vô hiệu hóa)</td>
            <td class="px-4 py-2 text-center">
              <button @click="confirmRestore" class="text-green-600 hover:text-green-800 font-bold text-xl leading-none">✔</button>
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
  methods: {
    getStockName(stockId) {
      const stock = this.stocks.find(s => s.id === stockId)
      return stock ? stock.name : '—'
    },
    onImageChange(e, index) {
      const file = e.target.files[0]
      if (file) {
        this.variants[index].image = file
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

      return attrObj.attributes.filter(a => used.includes(a.id)).map(a => ({
        valueId: a.id,
        valueTitle: a.title
      }))
    },
    confirmRestore() {
      if (this.restoringAttributes.includes('') || !this.restoringStock) {
        alert('Vui lòng chọn đầy đủ các giá trị')
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
</style>
