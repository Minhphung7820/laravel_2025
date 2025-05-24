<template>
  <div>
    <h2 class="text-lg font-semibold mb-2">Kho hàng</h2>
    <table class="w-full text-sm border border-gray-300">
      <thead class="bg-blue-50 text-left">
        <tr>
          <th class="border px-3 py-2">Chi nhánh (Kho)</th>
          <th class="border px-3 py-2 text-center">Tồn kho ban đầu</th>
          <th class="border px-3 py-2 text-center">Giá mua (₫)</th>
          <th class="border px-3 py-2 text-center">Giá bán (₫)</th>
          <th class="border px-3 py-2 text-center">% giảm tối đa</th>
          <th class="border px-3 py-2 text-center">% tăng tối đa</th>
          <th class="border px-3 py-2 text-center">Tự động tính</th>
          <th class="border px-3 py-2 text-center">Tính</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="stock in filteredStocks" :key="stock.id">
          <td class="border px-3 py-2">{{ stock.name }}</td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.id].qty" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.id].cost_price" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.id].sale_price" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.id].max_discount_percent" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="number" v-model.number="localData[stock.id].max_increase_percent" class="w-full px-1 py-1 text-xs border border-gray-300 rounded" />
          </td>
          <td class="border px-2 py-1 text-center">
            <input type="checkbox" v-model="localData[stock.id].auto_calc" />
          </td>
          <td class="border px-2 py-1 text-center">
            <button @click="calcSalePrice(stock.id)" class="bg-green-100 text-green-800 rounded px-2 py-1 text-xs font-semibold">Tính</button>
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
  },
  emits: ['update:modelValue'],
  data() {
    return {
      localData: {},
    }
  },
  computed: {
    filteredStocks() {
      return this.stocks.filter(stock => this.localData[stock.id])
    }
  },
  watch: {
   stocks: {
    immediate: true,
    handler(stocks) {
      stocks.forEach(stock => {
        if (!this.localData[stock.id]) {
          // Vue 3: Gán trực tiếp là reactive, không cần this.$set
          this.localData[stock.id] = {
            qty: 0,
            cost_price: 0,
            sale_price: 0,
            max_discount_percent: 0,
            max_increase_percent: 0,
            auto_calc: false,
          }
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
    calcSalePrice(stockId) {
      const row = this.localData[stockId]
      if (row.auto_calc && row.cost_price > 0) {
        const percent = row.max_increase_percent || 0
        row.sale_price = Math.round(row.cost_price * (1 + percent / 100))
      }
    }
  }
}
</script>
