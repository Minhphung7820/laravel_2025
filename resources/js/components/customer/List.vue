<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Danh sách khách hàng</h1>

    <CommonTable
      :columns="columns"
      :data="customers"
      :pagination="pagination"
      @search="onSearch"
      @page-change="onPageChange"
      :placeholder="'🔍 Tìm kiếm khách hàng...'"
      :withCheckbox="true"
      :isLoading="isLoading"
    >
      <template #buttons>
        <div class="flex gap-2 justify-end">
            <button
              class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
              @click="$router.push('/sale/customer/create')"
            >
              + Thêm khách hàng
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
import Filter from '@/components/customer/Filter.vue'

export default {
  name: 'ListCustomer',
  components: { CommonTable,FunnelIcon,Filter },
  data() {
    return {
      customerCache: {},
      showFilter: false,
      filters: {
        code: '',
        name: '',
        email: '',
        phone: '',
        from_date: '',
        to_date: '',
        type : ''
      },
      isLoading : true,
      customers: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10
      },
      columns: [
        { label: 'Ảnh', key: 'avatar_url',type : 'image_file' },
        { label: 'Tên khách hàng', key: 'name' },
        { label: 'Mã KH', key: 'code' },
        { label: 'Email', key: 'email' },
        { label: 'SĐT', key: 'phone' },
        { label: 'Địa chỉ', key: 'address_full' },
        { label: 'Loại khách hàng' , key: 'type', classMap: {
            'company': 'bg-purple-100 text-purple-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'individual': 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        }
      ],
      searchKeyword: '',
      dropdownId: null
    }
  },
  async mounted() {
    document.addEventListener('click', this.closeDropdown)
    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 10

      this.filters.name = decoded.name || ''
      this.filters.email = decoded.email || ''
      this.filters.phone = decoded.phone || ''
      this.filters.from_date = decoded.from_date || ''
      this.filters.to_date = decoded.to_date || ''
      this.filters.type = decoded.type || ''
    }

    await this.fetchCustomers(this.pagination.current_page)
  },
  beforeDestroy() {
     document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    makeCacheKey(page = this.pagination.current_page) {
      const filtersStr = JSON.stringify(this.filters)
      return `${this.searchKeyword}__page:${page}__filters:${filtersStr}`
    },
    onApplyFilter(values) {
      this.filters = values
      this.customerCache = {}
      this.fetchCustomers(1)
    },
    onResetFilter() {
      this.filters = {
        code: '',
        name: '',
        email: '',
        phone: '',
        from_date: '',
        to_date: '',
        type : ''
      }
      this.customerCache = {}
      this.fetchCustomers(1)
    },
    closeDropdown() {
      this.dropdownId = null
    },
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    onView(item) {
      this.$router.push(`/sale/customer/${item.id}/detail`)
      this.dropdownId = null
    },
    onEdit(item) {
      this.$router.push(`/sale/customer/${item.id}/edit`)
      this.dropdownId = null
    },
    onDelete(item) {
      if (confirm(`Xóa khách hàng: ${item.name}?`)) {
        console.log('Đã xoá:', item)
      }
      this.dropdownId = null
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
    async fetchCustomers(page = 1) {
      this.isLoading = true
      this.customers = []

      const cacheKey = this.makeCacheKey(page)
      if (this.customerCache[cacheKey]) {
        const { customers, pagination } = this.customerCache[cacheKey]
        this.customers = customers
        this.pagination = { ...pagination }
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
        const res = await window.axios.get('/api/customer/list', { params })
        this.customers = res.data.data.data

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

        this.customerCache[cacheKey] = {
          customers: this.customers,
          pagination: { ...this.pagination }
        }
      } catch (error) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },
    onSearch(keyword) {
      this.searchKeyword = keyword
      this.customerCache = {}
      this.fetchCustomers(1)
    },
    onPageChange(page) {
      this.fetchCustomers(page)
    }
  }
}
</script>
