<template>
  <div>
    <CommonTable
      :title="$t('unit.list_title')"
      :columns="columns"
      :data="units"
      :pagination="pagination"
      @search="onSearch"
      @page-change="fetchUnits"
      :placeholder="$t('unit.search_placeholder')"
      :isLoading="isLoading"
    >
      <template #buttons>
        <button
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold"
          @click="$router.push('/warehouse/unit/create')"
        >
          {{ $t('unit.add_button') }}
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
            class="absolute right-30 mt-2 bg-white border rounded shadow z-50 whitespace-nowrap px-2 py-1 min-w-[100px]"
          >
            <ul class="text-sm text-gray-700">
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item.id)">{{ $t('unit.view') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item.id)">{{ $t('unit.edit') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item.id)">{{ $t('unit.delete') }}</li>
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
      units: [],
      search: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
        per_page: 10,
      },
     columns: [
        { label: this.$t('unit.id'), key: 'id' },
        { label: this.$t('unit.name'), key: 'name' },
        { label: this.$t('unit.abbreviation'), key: 'abbreviation' }
      ],
      dropdownId: null
    }
  },
  computed: {
    ...mapGetters('cache', ['getCache'])
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    this.fetchUnits()
  },
  beforeDestroy(){
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
    async fetchUnits(page = 1) {
      this.isLoading = true
      this.units = []

      const cacheKey = this.makeCacheKey(page)
      const cached = this.getCache('unit', cacheKey)

      if (cached) {
        this.units = cached.units
        this.pagination = { ...cached.pagination }
        this.isLoading = false
        return
      }

      try {
        const res = await window.axios.get('/api/warehouse/unit/list', {
          params: { page, keyword: this.search }
        })

        this.units = res.data.data.data
        this.pagination = {
          current_page: res.data.data.current_page,
          last_page: res.data.data.last_page,
          from: res.data.data.from,
          to: res.data.data.to,
          total: res.data.data.total,
          per_page: res.data.data.per_page
        }

        this.setCache({
          module: 'unit',
          key: cacheKey,
          data: {
            units: this.units,
            pagination: { ...this.pagination }
          }
        })
      } catch (error) {
        console.error('Lỗi khi fetch units:', error)
      } finally {
        this.isLoading = false
      }
    },
    onSearch(keyword) {
      this.search = keyword
      this.fetchUnits(1)
    },
    onView(id) {
      this.$router.push(`/warehouse/unit/${id}/detail`)
    },
    onEdit(id) {
      this.$router.push(`/warehouse/unit/${id}/edit`)
    },
    onDelete(id) {
      if (confirm(this.$t('unit.confirm_delete'))) {
        this.clearModuleCache('unit')
        this.fetchUnits(1)
      }
    }
  }
}
</script>
