<template>
  <div v-if="visible" class="fixed inset-0 bg-gray-100 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-[800px] max-w-full p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">{{ $t('stock_table.select_stock') }}</h2>
        <button @click="$emit('close')" class="text-gray-600 hover:text-red-600 text-xl">×</button>
      </div>

      <div class="overflow-x-auto">
        <CommonTable
          :columns="columns"
          :data="stocks"
          :pagination="pagination"
          withCheckbox
          @selection-change="onSelect"
          @page-change="fetchStocks"
          :isLoading="isLoading"
        />
      </div>

      <div class="flex justify-end mt-6">
        <button @click="$emit('close')" class="mr-3 px-4 py-2 border rounded hover:bg-gray-100">{{ $t('stock_table.cancel') }}</button>
        <button @click="confirmSelection" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">{{ $t('stock_table.confirm') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
export default {
  name: 'AddStockModal',
  components: { CommonTable },
  props: {
    visible: Boolean,
    exceptIds: Array
  },
  watch: {
    visible(val) {
      if (val) {
        this.stocks = []
        this.selected = []
        this.pagination = {
          current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0
        }
        this.fetchStocks(1)
      }
    }
  },
  data() {
    return {
      isLoading : true,
      stocks: [],
      selected: [],
      pagination: {
        current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0
      },
      columns: [
        { key: 'name', label:  this.$t('stock_table.stock_name') }
      ]
    }
  },
  methods: {
    async fetchStocks(page = 1) {
     this.isLoading = true
     try {
        const except = this.exceptIds.join(',')
        const res = await fetch(`/api/warehouse/stock/list?page=${page}&except_ids=${except}`)
        const json = await res.json()
        this.stocks = json.data.data
        this.pagination = json.data
     } catch (error) {
        console.log(error);
     } finally {
        this.isLoading = false
     }
    },
    onSelect(list) {
      this.selected = list
    },
    confirmSelection() {
      this.$emit('add-stocks', this.selected)
      this.$emit('close')
    }
  }
}
</script>
