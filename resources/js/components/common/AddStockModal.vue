<template>
  <div v-if="visible" class="fixed inset-0 bg-gray-100 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-[800px] max-w-full">
      <div class="flex justify-end items-center mr-8 mt-8">
        <button @click="$emit('close')" class="text-red-600 hover:text-red-600 text-xl"><XMarkIcon class="w-6 h-6 text-white-500" /></button>
      </div>

      <div class="overflow-x-auto">
        <CommonTable
          :title="'Chọn kho'"
          :columns="columns"
          :data="stocks"
          :pagination="pagination"
          withCheckbox
          @selection-change="onSelect"
          @page-change="fetchStocks"
          :isLoading="isLoading"
        />
      </div>

      <div class="flex justify-end mb-8 mr-8 mt-8">
        <button @click="$emit('close')" class="mr-3 px-4 py-2 border rounded hover:bg-gray-100">{{ $t('stock_table.cancel') }}</button>
        <button @click="confirmSelection" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">{{ $t('stock_table.confirm') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import { XMarkIcon } from '@heroicons/vue/24/solid'

export default {
  name: 'AddStockModal',
  components: { CommonTable, XMarkIcon },
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
     this.stocks = []
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
