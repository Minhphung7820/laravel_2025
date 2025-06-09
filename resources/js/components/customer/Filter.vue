<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex justify-end">
    <!-- Overlay đằng sau -->
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal filter -->
    <div
      class="relative z-10 w-full max-w-sm bg-white shadow-xl h-full p-4 overflow-y-auto transition-all duration-300"
      @click.stop>
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Lọc khách hàng</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-xl cursor-pointer">&times;</button>
      </div>

      <!-- Filter Form -->
      <div class="space-y-4 text-sm text-gray-700">
        <div>
          <label class="block font-medium mb-1">Tên khách hàng</label>
          <input v-model="filter.name" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập tên'" />
        </div>
         <div>
          <label class="block font-medium mb-1">Email</label>
          <input v-model="filter.email" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập Email'" />
        </div>
         <div>
          <label class="block font-medium mb-1">SĐT</label>
          <input v-model="filter.phone" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập SĐT'" />
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block font-medium mb-1">Từ ngày</label>
            <input v-model="filter.from_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
          <div class="flex-1">
            <label class="block font-medium mb-1">Đến ngày</label>
            <input v-model="filter.to_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
        </div>
        <div>
          <label class="block font-medium mb-1">Loại khách hàng</label>
          <select v-model="filter.type" class="border rounded px-3 py-2 w-full shadow-sm text-sm">
            <option value="">-- Vui lòng chọn --</option>
            <option value="today">Công ty</option>
            <option value="individual">Cá nhân</option>
          </select>
        </div>
        <!-- Actions -->
        <div class="flex justify-between items-center pt-4">
          <button @click="onResetFilter" class="text-red-600 hover:underline text-sm cursor-pointer">Huỷ bộ lọc</button>
          <button @click="onApplyFilter" class="cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
            Lọc
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Filter',
    props: {
      visible: Boolean,
      value: Object
    },
    data() {
      return {
        filter: {
          name: '',
          email: '',
          phone: '',
          from_date: '',
          to_date: '',
          type : ''
        }
      }
    },
    watch: {
      value: {
        immediate: true,
        handler(val) {
          this.filter = {
            ...this.filter,
            ...val
          }
        }
      }
    },
    methods: {
      onResetFilter() {
        this.$emit('reset')
        this.$emit('close')
      },
      onApplyFilter() {
        this.$emit('apply', this.filter)
        this.$emit('close')
      }
    }
  }
</script>

<style scoped>
  /* Tùy chọn: animation mở modal */
</style>