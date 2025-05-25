<template>
  <div class="mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-blue-600">Lưới biến thể sản phẩm</h2>
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="(variant, index) in variants" :key="index" class="hover:bg-gray-50">
            <td v-for="attr in variant.attributes" :key="attr.id" class="px-4 py-2">{{ attr.value.title }}</td>
            <td class="px-4 py-2 text-center align-middle">
              <label class="cursor-pointer relative block w-16 h-16 mx-auto border border-gray-300 rounded overflow-hidden hover:shadow">
                <input
                  type="file"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                  @change="onImageChange($event, index)"
                  accept="image/*"
                />
                <img
                  v-if="variant.image"
                  :src="getImageUrl(variant.image)"
                  class="w-full h-full object-cover"
                />
                <img
                  v-else
                  src="https://static.thenounproject.com/png/1077596-200.png"
                  class="w-full h-full object-contain opacity-40"
                />
              </label>
            </td>
            <td class="px-4 py-2">{{ getStockName(variant.stock_id) }}</td>
             <td class="px-4 py-2">
              <input v-model="variant.quantity" type="number" min="0" class="w-24 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.purchase_price" type="number" min="0" class="w-24 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.sell_price" type="number" min="0" class="w-24 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.sku" type="text" class="w-32 px-2 py-1 border rounded" />
            </td>
            <td class="px-4 py-2">
              <input v-model="variant.barcode" type="text" class="w-32 px-2 py-1 border rounded" />
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
    variants: {
      type: Array,
      required: true
    },
    stocks: {
      type: Array,
      required: true
    },
    previewAttributes: Array
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
