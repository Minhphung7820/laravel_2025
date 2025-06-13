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
        <h2 class="text-lg font-semibold text-gray-800">Lọc nhà cung cấp</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-xl cursor-pointer">&times;</button>
      </div>

      <!-- Filter Form -->
      <div class="space-y-4 text-sm text-gray-700">
        <div>
          <label class="block font-medium mb-1">Mã nhà cung cấp</label>
          <input v-model="localFilter.code" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập mã KH'" />
        </div>
        <div>
          <label class="block font-medium mb-1">Tên nhà cung cấp</label>
          <input v-model="localFilter.name" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập tên'" />
        </div>
         <div>
          <label class="block font-medium mb-1">Email</label>
          <input v-model="localFilter.email" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập Email'" />
        </div>
         <div>
          <label class="block font-medium mb-1">SĐT</label>
          <input v-model="localFilter.phone" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="'Vui lòng nhập SĐT'" />
        </div>
        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block font-medium mb-1">Từ ngày</label>
            <input v-model="localFilter.from_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
          <div class="flex-1">
            <label class="block font-medium mb-1">Đến ngày</label>
            <input v-model="localFilter.to_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
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
      filter: {
        type: Object,
        required: true
      }
    },
    watch: {
      filter: {
        immediate: true,
        deep: true,
        handler(val) {
          this.localFilter = { ...val }
        }
      }
    },
    data() {
      return {
        localFilter: { ...this.filter }
      }
    },
    methods: {
      onResetFilter() {
        this.$emit('reset')
        this.$emit('close')
      },
      onApplyFilter() {
        this.$emit('apply', this.localFilter)
        this.$emit('close')
      }
    }
  }
</script>

<style scoped>
input,
select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
  line-height: 1.25rem;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

input:focus,
select:focus {
  outline: none;
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

</style>