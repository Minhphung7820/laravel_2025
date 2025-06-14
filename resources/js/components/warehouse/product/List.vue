<template>
  <div>
    <CommonTable
      :title="$t('product_list.title')"
      :columns="columns"
      :data="products"
      :pagination="pagination"
      :tabs="statusTabs"
      :withTabs="true"
      :current-tab="currentStatus"
      @change-tab="onChangeTab"
      @search="onSearch"
      @page-change="onPageChange"
      :placeholder="$t('product_list.search_placeholder')"
      @show-combo="handleShowCombo"
      :withCheckbox="true"
      :isLoading="isLoading"
    >
      <template #buttons>
        <div class="flex gap-2 justify-end">
          <button
            class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
            @click="$router.push('/warehouse/product/create/variable')"
          >
            {{ $t('product_list.add_product') }}
          </button>
          <button
            class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer"
            @click="$router.push('/warehouse/product/create/combo')"
          >
            {{ $t('product_list.add_combo_product') }}
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
        <div class="relative" @click.stop>
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
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item)">👁 {{ $t('actions.view') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item)">✏️ {{ $t('actions.edit') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item)">🗑 {{ $t('actions.delete') }}</li>
            </ul>
          </div>
        </div>
      </template>
    </CommonTable>
    <Filter
      :filter="filters"
      :visible="showFilter"
      @close="showFilter = false"
      @apply="onApplyFilter"
      @reset="onResetFilter"
    />
    <ComboQuickView
      :comboList="comboList"
      :visible="showCombo"
      @close="showCombo = false"
    />
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import Filter from '@/components/warehouse/product/Filter.vue'
import ComboQuickView from '@/components/warehouse/product/ComboQuickView.vue'
import { FunnelIcon } from '@heroicons/vue/24/solid'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'ListProduct',
  components: { CommonTable, Filter, FunnelIcon, ComboQuickView },
  computed: {
    ...mapGetters('cache', ['getCache'])
  },
  data() {
    return {
      isLoading: true,
      showFilter: false,
      filters: {
        supplier: '',
        brand: '',
        category: '',
        unit: '',
        from_date: '',
        to_date: '',
        time_filter: ''
      },
      products: [],
      currentStatus: 'all',
      statusTabs: [],
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
      columns: [
        { label: this.$t('product_list.image'), key: 'image', type: 'image_file' },
        { label: this.$t('product_list.product_name'), key: 'product_name' },
        { label: this.$t('product_list.sku'), key: 'sku' },
        { label: this.$t('product_list.stock'), key: 'stock_name' },
        {
          label: this.$t('product_list.type'), key: 'product_type', withLang: true, keyLang: 'product_type', classMap: {
            combo: 'bg-purple-100 text-purple-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            single: 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            variable: 'bg-blue-100 text-blue-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        },
        { label: this.$t('product_list.purchase_price'), key: 'purchase_price', isMoney: true },
        { label: this.$t('product_list.sell_price'), key: 'sell_price', isMoney: true },
        { label: this.$t('product_list.quantity'), key: 'quantity' },
        { label: this.$t('product_list.unit'), key: 'unit_name' },
        {
          label: this.$t('product_list.status'), key: 'status', withLang: true, keyLang: 'product_status', classMap: {
            pending: 'bg-yellow-100 text-yellow-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            approved: 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        }
      ],
      comboList: [],
      showCombo: false
    }
  },
  async mounted() {
    document.addEventListener('click', this.closeDropdown)

    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.currentStatus = decoded.status || 'all'
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 10
    }

    await this.fetchProducts(this.pagination.current_page)
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    ...mapActions('cache', ['setCache', 'clearModuleCache']),

    makeCacheKey(tab = this.currentStatus, page = this.pagination.current_page) {
      const filtersStr = JSON.stringify(this.filters)
      return `${tab}__${this.searchKeyword}__page:${page}__filters:${filtersStr}`
    },

    updateUrlQuery(page = 1) {
      const queryObj = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page,
        ...this.filters
      }

      if (['pending', 'approved', 'rejected'].includes(this.currentStatus)) {
        queryObj.status = this.currentStatus
      }

      const encoded = encodeQuery(queryObj)
      this.$router.replace({ path: this.$route.path, query: { query: encoded } })
    },

    async fetchProducts(page = 1) {
      this.isLoading = true
      this.products = []
      const cacheKey = this.makeCacheKey(this.currentStatus, page)
      const cached = this.getCache('product', cacheKey)

      if (cached) {
        this.products = cached.products
        this.pagination = { ...cached.pagination }
        this.statusTabs = cached.statusTabs || []
        this.updateUrlQuery(page)
        this.isLoading = false
        return
      }

      try {
        const params = {
          page,
          keyword: this.searchKeyword,
          limit: this.pagination.per_page,
          ...this.filters
        }

        if (['pending', 'approved', 'rejected'].includes(this.currentStatus)) {
          params.status = this.currentStatus
        }

        const [resList, resCount] = await Promise.all([
          window.axios.get('/api/warehouse/product/list', { params }),
          window.axios.get('/api/warehouse/product/get-status-count', { params })
        ])

        this.products = resList.data.data.data
        Object.assign(this.pagination, resList.data.data)
        this.statusTabs = resCount.data.data

        this.setCache({
          module: 'product',
          key: cacheKey,
          data: {
            products: this.products,
            pagination: { ...this.pagination },
            statusTabs: this.statusTabs
          }
        })

        this.updateUrlQuery(page)
      } catch (err) {
        console.error('Lỗi khi gọi API:', err)
      } finally {
        this.isLoading = false
      }
    },

    onApplyFilter(values) {
      this.filters = values
      this.fetchProducts(1)
    },

    onResetFilter() {
      this.filters = {
        supplier: '',
        brand: '',
        category: '',
        unit: '',
        from_date: '',
        to_date: '',
        time_filter: ''
      }
      this.fetchProducts(1)
    },

    onSearch(keyword) {
      this.searchKeyword = keyword
      this.fetchProducts(1)
    },

    onPageChange(page) {
      this.fetchProducts(page)
    },

    onChangeTab(status) {
      this.currentStatus = status
      this.fetchProducts(1)
    },

    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },

    closeDropdown() {
      this.dropdownId = null
    },

    handleShowCombo(item) {
      this.comboList = item.combo_list || []
      this.showCombo = true
    },

    onView(item) {
      // Tuỳ logic
    },

    onEdit(item) {
      this.$router.push(`/warehouse/product/edit/${item.product_id}/${item.product_type}`)
    },

    onDelete(item) {
      if (confirm(`${this.$t('actions.confirm_delete')} ${item.product_name}?`)) {
        // API delete
        alert(this.$t('actions.deleted'))
        this.clearModuleCache('product') // xóa toàn bộ cache
        this.fetchProducts(this.pagination.current_page)
      }
    }
  }
}
</script>
