<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Danh sách thương hiệu</h2>

    <CommonTable
      :columns="columns"
      :data="brands"
      :pagination="pagination"
      @search="onSearch"
      @page-change="fetchBrands"
      :placeholder="'🔍 Tìm kiếm thương hiệu...'"
    >
      <template #buttons>
        <button
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold"
          @click="$router.push('/warehouse/brand/create')"
        >
          + Thêm thương hiệu
        </button>
      </template>
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
            class="absolute right-20 mt-2 bg-white border rounded shadow z-50 whitespace-nowrap px-2 py-1 min-w-[100px]"
          >
            <ul class="text-sm text-gray-700">
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item.id)">👁 Xem</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item.id)">✏️ Sửa</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item.id)">🗑 Xóa</li>
            </ul>
          </div>
        </div>
      </template>
    </CommonTable>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'

export default {
  components: { CommonTable },
  data() {
    return {
      search: '',
      brands: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10,
      },
      columns: [
        { label: 'ID', key: 'id' },
        { label: 'Tên', key: 'name' },
        { label: 'Logo', key: 'logo_url',type : 'image_file' }
      ],
      dropdownId: null
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    this.fetchBrands()
  },
  beforeDestroy(){
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    closeDropdown() {
      this.dropdownId = null
    },
    async fetchBrands(page = 1) {
      const res = await window.axios.get('/api/warehouse/brand/list', {
        params: { page, keyword: this.search }
      })
      this.brands = res.data.data.data
      this.pagination = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        from: res.data.data.from,
        to: res.data.data.to,
        total: res.data.data.total,
        per_page: res.data.data.per_page
      }
    },
    onSearch(keyword) {
      this.search = keyword
      this.fetchBrands(1)
    },
    onView(id) {
      this.$router.push(`/warehouse/brand/${id}/detail`)
    },
    onEdit(id) {
      this.$router.push(`/warehouse/brand/${id}/edit`)
    },
    onDelete(id) {
      if (confirm('Xác nhận xoá?')) {
        // call API delete
      }
    }
  }
}
</script>
