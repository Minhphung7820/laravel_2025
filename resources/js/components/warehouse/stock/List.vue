<template>
  <div>
    <CommonTable
      :title="$t('stock.list_title')"
      :columns="columns"
      :data="stocks"
      :pagination="pagination"
      @search="onSearch"
      @page-change="fetchStocks"
      :placeholder="$t('stock.search_placeholder')"
      :isLoading="isLoading"
    >
      <template #buttons>
        <button
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold"
          @click="$router.push('/warehouse/stock/create')"
        >
          {{ $t('stock.add_button') }}
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
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item.id)">{{ $t('stock.view') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item.id)">{{ $t('stock.edit') }}</li>
              <li v-if="item.is_default !== 1" class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item.id)">{{ $t('stock.delete') }}</li>
            </ul>
          </div>
        </div>
      </template>
    </CommonTable>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: { CommonTable },
  data() {
    return {
      isLoading : true,
      stocks: [],
      search: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10,
      },
      dropdownId: null
    }
  },
  computed: {
     ...mapGetters('cache', ['getCache']),
    columns() {
      return [
        { label: this.$t('id'), key: 'id' },
        { label: this.$t('stock.name'), key: 'name' },
        { label: this.$t('stock.is_default'), key: 'is_default', withLang : true , keyLang : 'stock' , classMap: {
            0: 'bg-purple-100 text-purple-700 font-semibold px-2 py-1 rounded-full text-xs inline-block',
            1: 'bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full text-xs inline-block'
          }
        }
      ]
    }
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    this.fetchStocks()
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdown)
  },
  methods: {
      ...mapActions('cache', ['setCache', 'clearModuleCache']),
    makeCacheKey(page = this.pagination.current_page) {
      return `${this.search}__page:${page}`
    },
    toggleDropdown(id) {
      this.dropdownId = this.dropdownId === id ? null : id
    },
    closeDropdown() {
      this.dropdownId = null
    },
    async fetchStocks(page = 1) {
      this.isLoading = true
      this.stocks = []

      const cacheKey = this.makeCacheKey(page)
      const cached = this.getCache('stock', cacheKey)

      if (cached) {
        this.stocks = cached.stocks
        this.pagination = { ...cached.pagination }
        this.isLoading = false
        return
      }

      try {
        const res = await window.axios.get('/api/warehouse/stock/list', {
          params: { page, keyword: this.search }
        })

        this.stocks = res.data.data.data
        this.pagination = {
          current_page: res.data.data.current_page,
          last_page: res.data.data.last_page,
          from: res.data.data.from,
          to: res.data.data.to,
          total: res.data.data.total,
          per_page: res.data.data.per_page
        }

        this.setCache({
          module: 'stock',
          key: cacheKey,
          data: {
            stocks: this.stocks,
            pagination: { ...this.pagination }
          }
        })
      } catch (error) {
        console.error('Lỗi khi fetch stocks:', error)
      } finally {
        this.isLoading = false
      }
    },
    onSearch(keyword) {
      this.search = keyword
      this.fetchStocks(1)
    },
    onView(id) {
      this.$router.push(`/warehouse/stock/detail/${id}`)
    },
    onEdit(id) {
      this.$router.push(`/warehouse/stock/${id}/edit`)
    },
    onDelete(id) {
      if (confirm(this.$t('stock.confirm_delete'))) {
        this.clearModuleCache('stock')
        this.fetchStocks(1)
      }
    }
  }
}
</script>
