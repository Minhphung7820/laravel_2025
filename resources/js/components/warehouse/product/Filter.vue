<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex justify-end">
    <!-- Overlay đằng sau -->
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal filter -->
    <div
      class="relative z-10 w-full max-w-sm bg-white shadow-xl h-full p-4 overflow-y-auto transition-all duration-300"
      @click.stop
    >
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ $t('product_list.filter') }}</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-xl cursor-pointer">&times;</button>
      </div>

      <!-- Filter Form -->
      <div class="space-y-4 text-sm text-gray-700">
        <div>
          <label class="block font-medium mb-1">{{ $t('product_list.supplier') }}</label>
          <input v-model="filter.supplier" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="$t('product_list.supplier_placeholder')" />
        </div>

        <div>
          <label class="block font-medium mb-1">{{ $t('product_list.brand') }}</label>
          <input v-model="filter.brand" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="$t('product_list.brand_placeholder')" />
        </div>

        <div>
          <label class="block font-medium mb-1">{{ $t('product_list.category') }}</label>
          <input v-model="filter.category" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="$t('product_list.category_placeholder')" />
        </div>

        <div>
          <label class="block font-medium mb-1">{{ $t('product_list.unit') }}</label>
          <input v-model="filter.unit" class="border rounded px-3 py-2 w-full shadow-sm text-sm" :placeholder="$t('product_list.unit_placeholder')" />
        </div>

        <div class="flex gap-2">
          <div class="flex-1">
            <label class="block font-medium mb-1">{{ $t('product_list.from_date') }}</label>
            <input v-model="filter.from_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
          <div class="flex-1">
            <label class="block font-medium mb-1">{{ $t('product_list.to_date') }}</label>
            <input v-model="filter.to_date" type="date" class="border rounded px-3 py-2 w-full shadow-sm text-sm" />
          </div>
        </div>

        <div>
          <label class="block font-medium mb-1">{{ $t('product_list.time_filter') }}</label>
          <select v-model="filter.time_filter" class="border rounded px-3 py-2 w-full shadow-sm text-sm">
            <option value="">{{ $t('product_list.time_filter_placeholder') }}</option>
            <option value="today">{{ $t('product_list.today') }}</option>
            <option value="this_week">{{ $t('product_list.this_week') }}</option>
            <option value="this_month">{{ $t('product_list.this_month') }}</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4">
          <button @click="onResetFilter" class="text-red-600 hover:underline text-sm cursor-pointer">{{ $t('actions.reset_filter') }}</button>
          <button @click="$emit('apply', filter)" class="cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
            {{ $t('actions.filter') }}
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
        supplier: '',
        brand: '',
        category: '',
        unit: '',
        from_date: '',
        to_date: '',
        time_filter: ''
      }
    }
  },
  watch: {
    value: {
      immediate: true,
      handler(val) {
        this.filter = { ...this.filter, ...val }
      }
    }
  },
  methods: {
    onResetFilter() {
      this.$emit('reset')
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
/* Tùy chọn: animation mở modal */
</style>
