<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">{{ $t('product_list.title') }}</h1>

    <CommonTable
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
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import { encodeQuery, decodeQuery } from '@/utils/queryEncoder'

export default {
  name: 'ListProduct',
  components: { CommonTable },
  data() {
    return {
      products: [],
      currentStatus: 'all',
      statusTabs: [
        { key: 'all', label: 'product_status.all', count: 12 },
        { key: 'pending', label: 'product_status.pending', count: 5 },
        { key: 'approved', label: 'product_status.approved', count: 7 }
      ],
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
        { label: this.$t('product_list.product_name'), key: 'product_name' },
        { label: this.$t('product_list.image'), key: 'image', type: 'image_file' },
        { label: this.$t('product_list.sku'), key: 'sku' },
        { label: this.$t('product_list.stock'), key: 'stock_name' },
        { label: this.$t('product_list.type'), key: 'product_type' , withLang : true ,keyLang : 'product_type' ,classMap: {
            'combo': 'bg-purple-100 text-purple-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'single': 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'variable': 'bg-blue-100 text-blue-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        },
        { label: this.$t('product_list.purchase_price'), key: 'purchase_price' },
        { label: this.$t('product_list.sell_price'), key: 'sell_price' },
        { label: this.$t('product_list.quantity'), key: 'quantity' },
        { label: this.$t('product_list.unit'), key: 'unit_name' },
        { label: this.$t('product_list.status'), key: 'status',  withLang : true  ,keyLang : 'product_status' ,classMap: {
            'pending': 'bg-yellow-100 text-yellow-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            'approved': 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        }
      ]
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    const encoded = this.$route.query.query
    if (encoded) {
      const decoded = decodeQuery(encoded)
      this.searchKeyword = decoded.keyword || ''
      this.currentStatus = decoded.status || 'all'
      this.pagination.current_page = parseInt(decoded.page) || 1
      this.pagination.per_page = parseInt(decoded.limit) || 10
    }

    this.fetchProducts(this.pagination.current_page)
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
    onChangeTab(status) {
      this.currentStatus = status
      this.fetchProducts(1)
    },
    closeDropdown() {
      this.dropdownId = null
    },
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    onView(item) {
      // this.$route.push(`/warehouse/product/edit/${item.id}/${item.type}`)
    },
    onEdit(item) {
      this.$router.push(`/warehouse/product/edit/${item.product_id}/${item.product_type}`)
    },
    onDelete(item) {
      if (confirm(`${this.$t('actions.confirm_delete')} ${item.product_name}?`)) {
        alert(this.$t('actions.deleted'))
      }
    },
    updateUrlQuery(page = 1) {
      const queryObj = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }

      if (['pending', 'approved'].includes(this.currentStatus)) {
        queryObj.status = this.currentStatus
      }

      const encoded = encodeQuery(queryObj)
      this.$router.replace({
        path: this.$route.path,
        query: { query: encoded }
      })
    },
    fetchProducts(page = 1) {
      const params = {
        page,
        keyword: this.searchKeyword,
        limit: this.pagination.per_page
      }

      if (['pending', 'approved'].includes(this.currentStatus)) {
        params.status = this.currentStatus
      }

      this.updateUrlQuery(page)

      window.axios.get('/api/warehouse/product/list', { params }).then(res => {
        this.products = res.data.data.data
        Object.assign(this.pagination, res.data.data)
      })
    },
    onSearch(keyword) {
      this.searchKeyword = keyword
      this.fetchProducts(1)
    },
    onPageChange(page) {
      this.fetchProducts(page)
    }
  }
}
</script>
