<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">{{ $t('category.list_title') }}</h2>

    <TableList
      :columns="columns"
      :data="categories"
      :pagination="pagination"
      @page-change="fetchCategories"
      @search="onSearch"
      :placeholder="$t('category.search_placeholder')"
    >
      <!-- Nút thêm -->
      <template #buttons>
        <button
          @click="createCategory"
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
        >
         {{ $t('category.add_button') }}
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
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item)">{{ $t('category.action_view') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item)">{{ $t('category.action_edit') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item)">{{ $t('category.action_delete') }}</li>
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
        { label: this.$t('category.column_id'), key: 'id' },
        { label: this.$t('category.column_title'), key: 'title' },
        { label: this.$t('category.column_variants_count'), key: 'variants_count' }
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
        this.categories = res.data.data.data
        this.pagination = {
          current_page: res.data.data.current_page,
          last_page: res.data.data.last_page,
          from: res.data.data.from,
          to: res.data.data.to,
          total: res.data.data.total
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
      if (confirm(this.$t('category.confirm_delete', { title: item.title }))) {
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
