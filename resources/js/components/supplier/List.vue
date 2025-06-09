<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Danh sách nhà cung cấp</h1>

    <CommonTable
      :columns="columns"
      :data="suppliers"
      :pagination="pagination"
      @search="onSearch"
      @page-change="onPageChange"
      :placeholder="'🔍 Tìm kiếm nhà cung cấp...'"
      :isLoading="isLoading"
      :withCheckbox="true"
    >
      <template #buttons>
          <div class="flex gap-2 justify-end">
        <button
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
          @click="$router.push('/purchase/supplier/create')"
        >
          + Thêm nhà cung cấp
        </button>
             <button
                @click="showFilter = true"
                class="bg-white text-gray-700 px-4 py-2 border rounded-md hover:bg-gray-100 flex items-center gap-2 shadow-sm cursor-pointer"
              >
                <FunnelIcon class="w-5 h-5 text-gray-700" />
                {{ $t('actions.filter') }}
            </button>
             </div>
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
    </CommonTable>
  </div>
     <Filter
      :filter="filters"
      :visible="showFilter"
      @close="showFilter = false"
      @apply="onApplyFilter"
      @reset="onResetFilter"
    />
</template>

<script>
import CommonTable from '../common/TableList.vue'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'
import { FunnelIcon } from '@heroicons/vue/24/solid'
import Filter from '@/components/supplier/Filter.vue'
import { mapActions, mapGetters } from 'vuex'

export default {
  name: 'ListSupplier',
  components: { CommonTable, FunnelIcon, Filter },
  data() {
    return {
      showFilter: false,
      filters: {
        code: '',
        name: '',
        email: '',
        phone: '',
        from_date: '',
        to_date: ''
      },
      suppliers: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      },
      searchKeyword: '',
      dropdownId: null,
      isLoading: true,
      moduleKey: 'supplier',
      columns: [
        { label: 'Ảnh', key: 'avatar_url', type: 'image_file' },
        { label: 'Tên nhà cung cấp', key: 'name' },
        { label: 'Mã nhà cung cấp', key: 'code' },
        { label: 'Email', key: 'email' },
        { label: 'SĐT', key: 'phone' },
        { label: 'Địa chỉ', key: 'address_full' }
      ]
    }
  },
  computed: {
    ...mapGetters('cache', ['getCache'])
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)

    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 10

      Object.assign(this.filters, {
        code: decoded.code || '',
        name: decoded.name || '',
        email: decoded.email || '',
        phone: decoded.phone || '',
        from_date: decoded.from_date || '',
        to_date: decoded.to_date || ''
      })
    }

    this.fetchSuppliers(this.pagination.current_page)
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    ...mapActions('cache', ['setCache']),

    makeCacheKey(page = this.pagination.current_page) {
      const filtersStr = JSON.stringify(this.filters)
      return `${this.searchKeyword}__page:${page}__filters:${filtersStr}`
    },

    updateUrlQuery(page = 1) {
      const queryObj = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page,
        ...this.filters
      }
      const encoded = encodeQuery(queryObj)

      this.$router.replace({
        path: this.$route.path,
        query: { query: encoded }
      })
    },

    async fetchSuppliers(page = 1) {
      this.isLoading = true
      this.suppliers = []

      const cacheKey = this.makeCacheKey(page)
      const cached = this.getCache(this.moduleKey, cacheKey)

      if (cached) {
        this.suppliers = cached.suppliers
        this.pagination = { ...cached.pagination }
        this.updateUrlQuery(page)
        this.isLoading = false
        return
      }

      const params = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page,
        ...this.filters
      }

      this.updateUrlQuery(page)

      try {
        const res = await window.axios.get('/api/supplier/list', { params })
        this.suppliers = res.data.data.data

        const {
          current_page,
          last_page,
          from,
          to,
          per_page,
          total
        } = res.data.data

        Object.assign(this.pagination, {
          current_page,
          last_page,
          from,
          to,
          per_page,
          total
        })

        await this.setCache({
          module: this.moduleKey,
          key: cacheKey,
          data: {
            suppliers: this.suppliers,
            pagination: { ...this.pagination }
          }
        })
      } catch (error) {
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },

    // UI interactions
    onSearch(keyword) {
      this.searchKeyword = keyword
      this.fetchSuppliers(1)
    },
    onPageChange(page) {
      this.fetchSuppliers(page)
    },
    onApplyFilter(values) {
      this.filters = values
      this.fetchSuppliers(1)
    },
    onResetFilter() {
      this.filters = {
        code: '',
        name: '',
        email: '',
        phone: '',
        from_date: '',
        to_date: ''
      }
      this.fetchSuppliers(1)
    },

    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    closeDropdown() {
      this.dropdownId = null
    },
    onView(item) {
      this.$router.push(`/purchase/supplier/${item.id}/detail`)
      this.dropdownId = null
    },
    onEdit(item) {
      this.$router.push(`/purchase/supplier/${item.id}/edit`)
      this.dropdownId = null
    },
    onDelete(item) {
      if (confirm(`Xóa nhà cung cấp: ${item.name}?`)) {
        console.log('Đã xoá:', item)
      }
      this.dropdownId = null
    }
  }
}
</script>
