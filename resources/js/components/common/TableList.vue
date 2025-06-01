<template>
  <div class="p-4 bg-white rounded-xl shadow-md">
    <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
     <input
        v-model="search"
        @input="$emit('search', search)"
        type="text"
        :placeholder="placeholder"
        class="w-full md:w-[400px] px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
      <slot name="buttons"></slot>
    </div>

    <div class="rounded-lg border border-gray-200 overflow-visible">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100 sticky top-0 z-10">
          <tr>
            <th v-if="withCheckbox" class="px-4 py-3"><input type="checkbox" @change="toggleAll" :checked="allSelected" /></th>
            <th v-for="col in columns" :key="col.key" class="px-4 py-3 text-left font-semibold text-sm text-gray-700 uppercase tracking-wider">
              {{ col.label }}
            </th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="item in data"
            :key="item.id"
            class="hover:bg-blue-50 transition-all duration-200"
          >
            <td v-if="withCheckbox" class="px-4 py-2">
              <input type="checkbox" :value="item" v-model="selected" @change="$emit('selection-change', selected)" />
            </td>
            <td v-for="col in columns" :key="col.key" class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">
              <template v-if="col.type === 'image_file' && item[col.key]">
                <img :src="item[col.key]" class="w-10 h-10 rounded-full object-cover border" />
              </template>
              <template v-else>
                {{ item[col.key] }}
              </template>
            </td>
            <td class="px-4 py-2 text-center">
              <slot name="actions" :item="item" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination.total > pagination.per_page" class="mt-2 text-sm text-gray-500">
      Hiển thị {{ pagination.from }}–{{ pagination.to }} / {{ pagination.total }} dòng
    </div>

    <div v-if="pagination.total > pagination.per_page" class="flex flex-wrap gap-2 items-center justify-center md:justify-end">
      <button
        @click="$emit('page-change', 1)"
        :disabled="pagination.current_page === 1"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        « Đầu
      </button>
      <button
        @click="$emit('page-change', pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        ← Trước
      </button>

      <button
        v-for="p in pagesToShow"
        :key="p"
        :disabled="p === '...'"
        @click="$emit('page-change', p)"
        :class="[
          'px-3 py-1 border rounded cursor-pointer',
          p === pagination.current_page
            ? 'bg-blue-500 text-white font-semibold'
            : 'bg-white hover:bg-gray-100',
          p === '...' && 'cursor-default'
        ]"
      >
        {{ p }}
      </button>

      <button
        @click="$emit('page-change', pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        Sau →
      </button>
      <button
        @click="$emit('page-change', pagination.last_page)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        Cuối »
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CommonTable',
  props: {
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    pagination: {
      type: Object,
      default: () => ({
        current_page: 1,
        last_page: 1,
        per_page : 10,
        from: 0,
        to: 0,
        total: 0
      })
    },
    placeholder: {
      type: String,
      default: 'Nhập nội dung...'
    },
    withCheckbox: { type: Boolean, default: false }
  },
  data() {
    return {
      search: '',
      selected: []
    }
  },
  computed: {
    allSelected() {
      return this.data.length > 0 && this.selected.length === this.data.length
    },
    pagesToShow() {
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      const pages = []

      if (current > 3) pages.push(1)
      if (current > 4) pages.push('...')

      for (let i = current - 2; i <= current + 2; i++) {
        if (i > 0 && i <= last) {
          pages.push(i)
        }
      }

      if (current < last - 3) pages.push('...')
      if (current < last - 2) pages.push(last)

      return pages
    }
  },
  methods: {
    toggleAll(e) {
      this.selected = e.target.checked ? [...this.data] : []
      this.$emit('selection-change', this.selected)
    }
  }
}
</script>

<style scoped>

</style>
