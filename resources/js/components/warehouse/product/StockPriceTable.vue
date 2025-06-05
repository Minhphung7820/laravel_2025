<template>
  <div>

    <div class="flex justify-between items-center mb-2">
      <h2 class="text-lg font-semibold mb-2">{{ $t('stock_table.title') }}</h2>
      <button
        @click="$emit('open-add-stock-modal')"
        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm"
      >
        + {{ $t('stock_table.add_stock') }}
      </button>
    </div>
    <table class="w-full text-sm border border-gray-300">
      <thead class="bg-blue-50 text-left">
        <tr>
          <th class="border px-3 py-2">{{ $t('stock_table.branch') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.initial_quantity') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.purchase_price') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.sell_price') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.max_discount') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.max_increase') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.auto_calc') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.calc') }}</th>
          <th class="border px-3 py-2 text-center">{{ $t('stock_table.remove') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="stock in filteredStocks" :key="stock.stock_id">
          <td class="border px-3 py-2">
            <span>{{ stock.name }}</span>
            <span
              v-if="localData[stock.stock_id]?.is_default === 1"
              class="ml-2 inline-block bg-blue-100 text-blue-700 text-[12px] font-medium px-2 py-[1px] rounded-full"
            >
              {{ $t('stock_table.default') }}
            </span>
          </td>
          <td class="border px-2 py-1 text-center">
            <input
              type="number"
              v-if="!isVariableProduct"
              v-model.number="localData[stock.stock_id].qty"
              class="w-full px-1 py-1 text-xs border border-gray-300 rounded"
            />
            <span v-else>{{ variantStockTotals[stock.stock_id] || 0 }}</span>
          </td>
          <td class="border px-2 py-1 text-center">
            <span v-if="isVariableProduct" class="block w-full py-1 text-xs text-gray-400">—</span>
            <input
              v-else
              v-model.number="localData[stock.stock_id].purchase_price"
              type="number"
              class="w-full px-1 py-1 text-xs border border-gray-300 rounded"
            />
          </td>
          <td class="border px-2 py-1 text-center">
            <span v-if="isVariableProduct" class="block w-full py-1 text-xs text-gray-400">—</span>
            <input
              v-else
              v-model.number="localData[stock.stock_id].sell_price"
              type="number"
              class="w-full px-1 py-1 text-xs border border-gray-300 rounded"
            />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.stock_id].max_discount_percent" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.stock_id].max_increase_percent" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="checkbox" v-model="localData[stock.stock_id].auto_calc" />
          </td>
          <td class="border px-2 py-1 text-center">
            <button @click="calcSalePrice(stock.stock_id)" class="bg-green-100 text-green-800 rounded px-2 py-1 text-xs font-semibold">{{ $t('stock_table.calc') }}</button>
          </td>
          <td class="border px-2 py-1 text-center">
            <button
              v-if="!localData[stock.stock_id].is_default"
              @click="removeStock(stock.stock_id)"
              class="text-red-500 hover:text-red-700 text-sm font-bold"
            >
              ×
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'StockPriceTable',
  props: {
    stocks: Array,
    modelValue: Object,
    isVariableProduct: Boolean,
    variantStockTotals: Object
  },
  emits: ['update:modelValue', 'remove-stock', 'open-add-stock-modal'],
  data() {
    return {
      localData: {},
    }
  },
  computed: {
    filteredStocks() {
      return this.stocks.filter(stock => this.localData[stock.stock_id])
    }
  },
  watch: {
   stocks: {
    immediate: true,
    handler(stocks) {
      stocks.forEach(stock => {
        const existing = this.modelValue[stock.stock_id] || {}
        this.localData[stock.stock_id] = {
          id: existing.id ?? null,
          stock_id : stock.id,
          qty: existing.qty ?? 0,
          purchase_price: existing.purchase_price ?? 0,
          sell_price: existing.sell_price ?? 0,
          max_discount_percent: existing.max_discount_percent ?? 0,
          max_increase_percent: existing.max_increase_percent ?? 0,
          auto_calc: existing.auto_calc ?? false,
          is_default: existing.is_default ?? 0,
        }
       })
     }
    },
    localData: {
      deep: true,
      handler(val) {
        this.$emit('update:modelValue', val)
      }
    }
  },
  methods: {
    removeStock(stockId) {
      const stock = this.localData[stockId]
      if (stock?.is_default) {
        return
      }

      delete this.localData[stockId]
      this.$emit('remove-stock', stockId)
    },
    calcSalePrice(stockId) {
      const row = this.localData[stockId]
      if (row.auto_calc && row.purchase_price > 0) {
        const percent = row.max_increase_percent || 0
        row.sell_price = Math.round(row.purchase_price * (1 + percent / 100))
      }
    }
  }
}
</script>
