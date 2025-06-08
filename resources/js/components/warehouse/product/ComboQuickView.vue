<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex justify-center items-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal chính giữa -->
    <div
      class="relative z-10 w-full max-w-2xl bg-white shadow-xl max-h-[80vh] rounded-lg flex flex-col transition-all duration-300"
      @click.stop
    >
      <!-- Header cố định -->
      <div class="sticky top-0 bg-white px-6 py-4 border-b flex justify-between items-center z-10">
        <h2 class="text-lg font-semibold text-gray-800">{{ $t('combo_modal.title') }}</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-xl cursor-pointer">&times;</button>
      </div>

      <!-- Nội dung scroll -->
      <div class="overflow-y-auto px-6 py-4 flex-1">
        <div
          v-for="combo in comboItems"
          :key="combo.id"
          class="border-b py-4 flex gap-4"
        >
          <img :src="getImage(combo)" class="w-16 h-16 rounded border object-cover" />
          <div>
            <div class="font-semibold text-sm">
              {{ getProductName(combo) }}
            </div>
            <div class="text-sm text-gray-600">{{ $t('combo_modal.sku') }}: {{ getSku(combo) }}</div>
            <div class="text-sm text-gray-600"> {{ getComboPriceText(combo) }}</div>
          </div>
        </div>

        <div v-if="hasMoreCombo" class="text-center mt-4">
          <button @click="loadMoreCombo" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 cursor-pointer">
             {{ $t('combo_modal.load_more') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { formatCurrency } from '@/utils/currency'

export default {
  props: {
    comboList: Array,
    visible: Boolean
  },
  data() {
    return {
      comboItems: [],
      page: 1,
      perPage: 10
    }
  },
  computed: {
    hasMoreCombo() {
      return this.comboList.length > this.page * this.perPage
    }
  },
  watch: {
    visible(val) {
      if (val) this.resetData()
    }
  },
  methods: {
    formatCurrency,
    getComboPriceText(combo) {
      return this.$t('combo_modal.quantity_price', {
        quantity: combo.quantity_combo,
        price: this.formatCurrency(combo.sell_price_combo, this.$i18n.locale)
      })
    },
    resetData() {
      this.page = 1
      this.comboItems = this.comboList.slice(0, this.perPage)
    },
    loadMoreCombo() {
      this.page++
      this.comboItems = this.comboList.slice(0, this.page * this.perPage)
    },
    getImage(combo) {
      const p = combo.parent
      if (!p) return ''
      return p.product?.type === 'single'
        ? p.product.image_cover_url
        : p.variant_images?.[0]?.image || 'https://static.thenounproject.com/png/1077596-200.png'
    },
    getSku(combo) {
      const p = combo.parent
      return p?.product?.type === 'single' ? p.product.sku : p.sku
    },
    getProductName(combo) {
      const p = combo.parent
      if (!p || !p.product) return ''
      let name = p.product.name
      if (p.product.type === 'variable') {
        if (p.attribute_first?.title) name += ` - ${p.attribute_first.title}`
        if (p.attribute_second?.title) name += ` - ${p.attribute_second.title}`
      }
      return name
    }
  }
}
</script>

<style scoped>
/* Optional: thêm hiệu ứng fade nếu cần */
</style>
