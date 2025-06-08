<template>
  <div class="p-4 bg-white rounded-xl shadow-md">
    <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
      <input
        v-model="search"
        @input="$emit('search', search)"
        type="text"
        :placeholder="placeholder"
        class="w-full md:w-[400px] px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        :disabled="isLoading"
      />
      <slot name="buttons" />
    </div>

    <!-- Tabs Section -->
    <div v-if="withTabs" class="flex flex-wrap gap-2 mb-4">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="$emit('change-tab', tab.key)"
        :class="[
          'px-4 py-1.5 rounded-full font-medium text-sm border transition-all duration-200 flex items-center gap-1 cursor-pointer',
          tab.key === currentTab
            ? 'bg-blue-600 text-white border-blue-600 shadow'
            : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-100'
        ]"
      >
        {{ $t(tab.label) }}
        <span
          v-if="tab.count !== undefined"
          class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full shadow-sm ring-2 ring-inset"
          :class="tab.key === currentTab
            ? 'bg-white text-blue-700 ring-blue-500'
            : 'bg-gray-200 text-gray-800 ring-gray-300'"
        >
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Table Section -->
    <div class="rounded-lg border border-gray-200 overflow-visible">
      <!-- (table unchanged) -->
    </div>

    <div class="rounded-lg border border-gray-200 overflow-visible">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100 sticky top-0 z-10">
          <tr>
            <th v-if="withCheckbox" class="w-12 px-4 py-3">
              <input :disabled="isLoading" type="checkbox" @change="toggleAll" :checked="isAllCheckedOnPage" />
            </th>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-left font-semibold text-sm text-gray-700 uppercase tracking-wider"
            >
              {{ col.label }}
            </th>
            <th v-if="hasActions" class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white" :class="{ 'min-h-[300px]': !isLoading && data.length === 0 }">
          <tr v-if="isLoading">
            <td
              :colspan="(columns.length + (withCheckbox ? 1 : 0) + (hasActions ? 1 : 0))"
              class="text-center text-gray-400 py-8"
            >
              <div class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8z" />
                </svg>
                <!-- {{ $t('table.loading') }} -->
              </div>
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td
              :colspan="(columns.length + (withCheckbox ? 1 : 0) + (hasActions ? 1 : 0))"
              class="text-center text-gray-500 py-8"
            >
              {{ $t('table.no_data') }}
            </td>
          </tr>
          <tr
            v-for="item in data"
            :key="item.id"
            class="hover:bg-blue-50 transition-all duration-200"
          >
            <td v-if="withCheckbox" class="w-12 px-4 py-2">
              <input
                type="checkbox"
                :value="item.id"
                :checked="isSelected(item.id)"
                @change="toggleSelection($event, item)"
              />
            </td>
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap overflow-hidden text-ellipsis max-w-[200px]"
              :title="item[col.key]"
            >
              <template v-if="col.type === 'image_file' && item[col.key]">
                <img :src="item[col.key]" class="w-10 h-10 rounded-full object-cover border" />
              </template>
              <template v-else-if="col.key === 'product_name'">
                <div>
                  <div>{{ getCellText(item, col) }}</div>
                  <button
                    v-if="item.product_type === 'combo'"
                    @click="$emit('show-combo', item)"
                    class="text-blue-600 text-sm mt-1 hover:underline cursor-pointer"
                  >
                    {{ $t('table.view_combo_detail') }}
                  </button>
                </div>
              </template>
              <template v-else-if="col.isMoney">
                  {{ formatCurrency(getCellText(item, col), $i18n.locale) }}
              </template>
              <template v-else>
                <component
                  :is="col.wrapperTag || 'span'"
                  :class="getCellClass(item, col)"
                >
                  {{ getCellText(item, col) }}
                </component>
              </template>
            </td>
            <td v-if="hasActions" class="px-4 py-2 text-center">
              <slot name="actions" :item="item" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination.total > pagination.per_page" class="mt-2 text-sm text-gray-500">
      {{ $t('table.showing_rows', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
    </div>

    <div v-if="pagination.total > pagination.per_page" class="flex flex-wrap gap-2 items-center justify-center md:justify-end">
      <button
        @click="$emit('page-change', 1)"
        :disabled="pagination.current_page === 1"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        « {{ $t('table.first') }}
      </button>
      <button
        @click="$emit('page-change', pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        ← {{ $t('table.prev') }}
      </button>

      <button
        v-for="p in pagesToShow"
        :key="p"
        :disabled="p === '...'"
        @click="$emit('page-change', p)"
        :class="[
          'px-3 py-1 border rounded cursor-pointer',
          p === pagination.current_page
            ? 'bg-blue-500 text-white font-semibold'
            : 'bg-white hover:bg-gray-100',
          p === '...' && 'cursor-default'
        ]"
      >
        {{ p }}
      </button>

      <button
        @click="$emit('page-change', pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        {{ $t('table.next') }} →
      </button>
      <button
        @click="$emit('page-change', pagination.last_page)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 cursor-pointer"
      >
        {{ $t('table.last') }} »
      </button>
    </div>
  </div>
</template>

<script>
import { formatCurrency } from '@/utils/currency'
export default {
  name: 'CommonTable',
  props: {
    isLoading: { type: Boolean, default: false },
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    pagination: { type: Object, default: () => ({ current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0 }) },
    placeholder: { type: String, default: 'Nhập nội dung...' },
    withCheckbox: { type: Boolean, default: false },
    hasActions: { type: Boolean, default: true },
    withTabs: { type: Boolean, default: false },
    tabs: {
      type: Array,
      default: () => []
    },
    currentTab: {
      type: String,
      default: 'all'
    }
  },
  data() {
    return {
      search: '',
      selectedIds: [],
      selectedItems: []
    }
  },
  computed: {
    isAllCheckedOnPage() {
      return this.data.length > 0 && this.data.every(item => this.selectedIds.includes(item.id))
    },
    pagesToShow() {
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      const pages = []

      if (current > 3) pages.push(1)
      if (current > 4) pages.push('...')

      for (let i = current - 2; i <= current + 2; i++) {
        if (i > 0 && i <= last) {
          pages.push(i)
        }
      }

      if (current < last - 3) pages.push('...')
      if (current < last - 2) pages.push(last)

      return pages
    }
  },
  methods: {
    formatCurrency,
    isSelected(id) {
      return this.selectedIds.includes(id)
    },
    toggleSelection(event, item) {
      const checked = event.target.checked
      if (checked) {
        if (!this.selectedIds.includes(item.id)) {
          this.selectedIds.push(item.id)
          this.selectedItems.push(item)
        }
      } else {
        const index = this.selectedIds.indexOf(item.id)
        if (index !== -1) {
          this.selectedIds.splice(index, 1)
          this.selectedItems = this.selectedItems.filter(i => i.id !== item.id)
        }
      }
      this.$emit('selection-change', this.selectedItems)
    },
    toggleAll(e) {
      const checked = e.target.checked
      if (checked) {
        this.data.forEach(item => {
          if (!this.selectedIds.includes(item.id)) {
            this.selectedIds.push(item.id)
            this.selectedItems.push(item)
          }
        })
      } else {
        this.data.forEach(item => {
          const index = this.selectedIds.indexOf(item.id)
          if (index !== -1) {
            this.selectedIds.splice(index, 1)
            this.selectedItems = this.selectedItems.filter(i => i.id !== item.id)
          }
        })
      }
      this.$emit('selection-change', this.selectedItems)
      console.log(this.selectedIds);

    },
    getCellText(item, col) {
      if (!col.withLang || !col.keyLang) return item[col.key]
      const fullKey = `${col.keyLang}.${item[col.key]}`
      return this.$t(fullKey)
    },
    getCellClass(item, col) {
      if (!col.classMap) return ''
      const value = item[col.key]
      return col.classMap[value] || ''
    }
  }
}
</script>

<style scoped>

</style>
