<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex justify-center items-center">
    <!-- Overlay nền mờ -->
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal nội dung chính -->
    <div class="relative z-10 bg-white w-[90%] max-w-[1200px] rounded-xl shadow-lg p-6" @click.stop>
      <!-- Bảng chọn kho -->
      <CommonTable
        :title="'+Thêm nhanh kho'"
        :columns="columns"
        :data="stocks"
        :pagination="pagination"
        withCheckbox
        :placeholder="'Nhập nội dung...'"
        @selection-change="onSelect"
        @page-change="fetchStocks"
        :isLoading="isLoading"
      />

      <!-- Nút thao tác -->
      <div class="flex justify-end gap-2 pt-6">
        <button @click="$emit('close')" class="px-4 py-1 rounded bg-red-300 text-white hover:bg-red-400">
          {{ $t('stock_table.cancel') || 'Hủy' }}
        </button>
        <button @click="confirmSelection" class="px-4 py-1 rounded bg-blue-600 text-white hover:bg-green-700">
          {{ $t('stock_table.confirm') || 'Thêm' }}
        </button>
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
      isLoading: true,
      stocks: [],
      selected: [],
      pagination: {
        current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0
      },
      columns: [
        { key: 'name', label: this.$t('stock_table.stock_name') || 'Tên kho' }
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
        console.log(error)
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
