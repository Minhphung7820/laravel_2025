<template>
  <div>
    <div class="mb-4 flex justify-between items-center">
      <div>
       <input
          v-model="search"
          @input="$emit('search', search)"
          type="text"
          placeholder="Tìm kiếm..."
          class="border px-3 py-1 rounded"
        />
      </div>
      <slot name="actions"></slot>
    </div>

    <table class="w-full border border-gray-300">
      <thead>
        <tr class="bg-gray-100">
          <th v-for="col in columns" :key="col.key" class="text-left p-2 border-b">
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data" :key="item.id" class="border-b hover:bg-gray-50">
          <td v-for="col in columns" :key="col.key" class="p-2">
            {{ item[col.key] }}
          </td>
        </tr>
        <tr v-if="!data.length">
          <td :colspan="columns.length" class="text-center py-4 text-gray-500">
            Không có dữ liệu
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
      <div>
        Trang {{ pagination.current_page }} / {{ pagination.last_page }}
      </div>
      <div class="space-x-2">
        <button
          @click="$emit('page-change', pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50"
        >
          Trước
        </button>
        <button
          @click="$emit('page-change', pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50"
        >
          Sau
        </button>
      </div>
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
      default: () => ({ current_page: 1, last_page: 1 })
    }
  },
  data() {
    return {
      search: ''
    }
  }
}
</script>

<style scoped>
input[type="text"] {
  width: 200px;
}
</style>
