<template>
  <div>
    <CommonTable
      :title="'Danh sách báo giá'"
      :columns="columns"
      :data="brands"
      :pagination="pagination"
      @search="onSearch"
      @page-change="fetchBrands"
      :placeholder="'Tìm kiếm báo giá'"
      :isLoading="isLoading"
    >
      <template #buttons>
         <div class="flex gap-2 justify-end">
              <button
                class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold"
                @click="$router.push('/sale/price-quotation-order/create')"
              >
                + Tạo báo giá
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
            class="absolute right-20 mt-2 bg-white border rounded shadow z-50 whitespace-nowrap px-2 py-1 min-w-[100px]"
          >
            <ul class="text-sm text-gray-700">
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onView(item.id)">{{ $t('brand.action_view') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer" @click="onEdit(item.id)">{{ $t('brand.action_edit') }}</li>
              <li class="hover:bg-gray-100 px-4 py-2 cursor-pointer text-red-500" @click="onDelete(item.id)">{{ $t('brand.action_delete') }}</li>
            </ul>
          </div>
        </div>
      </template>
    </CommonTable>
  </div>
</template>

<script>
import CommonTable from '@/components/common/TableList.vue'
import { mapActions, mapGetters } from 'vuex'
import { FunnelIcon } from '@heroicons/vue/24/solid'
export default {
  components: { CommonTable,FunnelIcon },
  data() {
    return {
      isLoading : true,
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
        { label: this.$t('brand.column_id'), key: 'id' },
        { label: this.$t('brand.column_name'), key: 'name' },
        { label: this.$t('brand.column_logo'), key: 'logo_url',type : 'image_file' }
      ],
      dropdownId: null
    }
  },
  computed: {
    ...mapGetters('cache', ['getCache'])
  },
  mounted() {
    document.addEventListener('click', this.closeDropdown)
    this.fetchPriceQuotations()
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
    async fetchPriceQuotations(page = 1) {

    },
    onSearch(keyword) {
      this.search = keyword
      this.fetchPriceQuotations(1)
    },
    onView(id) {
      this.$router.push(`/warehouse/brand/${id}/detail`)
    },
    onEdit(id) {
      this.$router.push(`/warehouse/brand/${id}/edit`)
    },
    onDelete(id) {
      if (confirm(this.$t('brand.confirm_delete'))) {
        this.clearModuleCache('brand')
        this.fetchPriceQuotations(1)
      }
    }
  }
}
</script>
