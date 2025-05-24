<template>
  <div class="space-y-4">
    <h2 class="text-xl font-semibold">Biến thể sản phẩm</h2>

    <div v-if="variants.length === 0" class="text-sm text-gray-500">
      Chưa có biến thể nào được chọn.
    </div>

    <div v-else>
      <table class="min-w-full text-sm border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-2 py-1">#</th>
            <th class="border px-2 py-1">Thuộc tính</th>
            <th class="border px-2 py-1">SKU</th>
            <th class="border px-2 py-1">Barcode</th>
            <th class="border px-2 py-1">Ảnh</th>
            <th class="border px-2 py-1 text-center">Đang mở bán</th>
            <th v-for="stock in stocks" :key="stock.id" class="border px-2 py-1 text-center">
              {{ stock.name }}<br><small class="text-gray-500">(SL / Mua / Bán)</small>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(variant, index) in variants" :key="variant.key">
            <td class="border px-2 py-1 text-center">{{ index + 1 }}</td>
            <td class="border px-2 py-1">{{ variant.label }}</td>
            <td class="border px-2 py-1">
              <input v-model="variant.sku" class="w-full px-1 py-1 border border-gray-300 rounded text-xs" />
            </td>
            <td class="border px-2 py-1">
              <input v-model="variant.barcode" class="w-full px-1 py-1 border border-gray-300 rounded text-xs" />
            </td>
            <td class="border px-2 py-1">
              <input type="file" @change="e => handleImage(e, variant)" />
              <div v-if="variant.image" class="mt-1">
                <img :src="getPreview(variant.image)" class="w-12 h-12 object-cover rounded border" />
              </div>
            </td>
            <td class="border px-2 py-1 text-center">
              <input type="checkbox" v-model="variant.is_active" />
            </td>
            <td
              v-for="stock in stocks"
              :key="stock.id"
              class="border px-1"
            >
              <div class="flex flex-col gap-1">
                <input
                  v-model.number="variant.stocks[stock.id].qty"
                  placeholder="SL"
                  class="w-full px-1 py-1 border border-gray-300 rounded text-xs text-center"
                />
                <input
                  v-model.number="variant.stocks[stock.id].cost_price"
                  placeholder="Mua"
                  class="w-full px-1 py-1 border border-gray-300 rounded text-xs text-center"
                />
                <input
                  v-model.number="variant.stocks[stock.id].sale_price"
                  placeholder="Bán"
                  class="w-full px-1 py-1 border border-gray-300 rounded text-xs text-center"
                />
              </div>
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
    stocks: Array,
    variants: Array,
  },
  methods: {
    handleImage(e, variant) {
      const file = e.target.files[0]
      variant.image = file
    },
    getPreview(file) {
      if (typeof file === 'string') return file
      return file ? URL.createObjectURL(file) : null
    },
  },
}
</script>
