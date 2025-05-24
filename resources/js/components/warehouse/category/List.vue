<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">📂 Danh sách danh mục</h2>

    <TableList
      :columns="columns"
      :data="categories"
      :pagination="pagination"
      @page-change="fetchCategories"
      @search="onSearch"
    >
      <!-- Nút thêm -->
      <template #buttons>
        <button
          @click="createCategory"
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
        >
          + Thêm danh mục
        </button>
      </template>

      <!-- Cột action -->
      <template #actions="{ item }">
        <div v-if="item && item.id" class="relative" @click.stop>
          <button
            @click="toggleDropdown(item.id)"
            class="px-2 py-1 rounded hover:bg-gray-100 focus:outline-none cursor-pointer"
          >
            ⋯
          </button>

          <div
            v-if="dropdownId === item.id"
            class="absolute right-0 mt-2 bg-white border rounded shadow z-50 whitespace-nowrap px-2 py-1 min-w-[100px]"
          >
            <ul class="text-sm text-gray-700">
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item)">👁 Xem</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item)">✏️ Sửa</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item)">🗑 Xóa</li>
            </ul>
          </div>
        </div>
      </template>
    </TableList>
  </div>
</template>

<script>
import TableList from '@/components/common/TableList.vue'

export default {
  components: { TableList },
  data() {
    return {
      categories: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      },
      columns: [
        { label: 'ID', key: 'id' },
        { label: 'Tên danh mục', key: 'title' },
        { label: 'Số lượng biến thể', key: 'variants_count' }
      ],
      keyword: '',
      dropdownId: null
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    this.fetchCategories(1)
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    closeDropdown() {
      this.dropdownId = null
    },
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    async fetchCategories(page = 1) {
      try {
        const res = await window.axios.get('/api/warehouse/category/list', {
          params: { page, keyword: this.keyword }
        })
        this.categories = res.data.data
        this.pagination = {
          current_page: res.data.current_page,
          last_page: res.data.last_page,
          from: res.data.from,
          to: res.data.to,
          total: res.data.total
        }
      } catch (e) {
        console.error('Lỗi khi lấy danh sách danh mục:', e)
      }
    },
    onSearch(keyword) {
      this.keyword = keyword
      this.fetchCategories(1)
    },
    onView(item) {
      this.$router.push(`/warehouse/category/${item.id}/detail`)
      this.dropdownId = null
    },
    onEdit(item) {
      this.$router.push(`/warehouse/category/${item.id}/edit`)
      this.dropdownId = null
    },
    onDelete(item) {
      if (confirm(`Xóa danh mục: ${item.title}?`)) {
        console.log('Đã xoá:', item)
      }
      this.dropdownId = null
    },
    createCategory() {
      this.$router.push(`/warehouse/category/create`)
    }
  }
}
</script>
