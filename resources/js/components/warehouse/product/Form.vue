<template>
  <div class="space-y-6 p-4 bg-white rounded-xl shadow-md">
    <h1 class="text-2xl font-bold">
      {{ mode === 'update' ? $t('product.update_title') : $t('product.create_title') }}
    </h1>
    <!-- Thông tin chung -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">{{ $t('product.name') }} *</label>
        <input v-model="form.name" type="text" :placeholder="$t('common.please_enter')" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label class="block font-semibold">{{ $t('product.sku') }}</label>
        <input v-model="form.sku" type="text" :placeholder="$t('common.please_enter')" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label class="block font-semibold">{{ $t('product.barcode') }}</label>
        <input v-model="form.barcode" type="text" :placeholder="$t('common.please_enter')" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
    </div>
    <div>
      <label class="inline-flex items-center">
        <input type="checkbox" v-model="form.has_serial" class="mr-2" />
        {{ $t('product.has_serial') }}
      </label>
    </div>
    <div>
      <label class="block font-semibold">{{ $t('product.warranty') }}</label>
      <select
        v-model="form.warranty"
        class="w-full mt-2 px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">-- {{ $t('common.please_select') }} --</option>
        <option value="6_months">6 {{ $t('common.months') }}</option>
        <option value="12_months">12 {{ $t('common.months') }}</option>
        <option value="1_year">1 {{ $t('common.year') }}</option>
        <option value="5_years">5 {{ $t('common.years') }}</option>
        <option value="10_years">10 {{ $t('common.years') }}</option>
        <option value="21_years">21 {{ $t('common.years') }}</option>
      </select>
    </div>
    <!-- Phân loại -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">{{ $t('product.unit') }}</label>
        <select v-model="form.unit_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">{{ $t('common.please_select') }}</option>
          <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold">{{ $t('product.brand') }}</label>
        <select v-model="form.brand_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">{{ $t('common.please_select') }}</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold">{{ $t('product.category') }}</label>
        <select @change="onCategoryChange" v-model="form.category_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">{{ $t('common.please_select') }}</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
        </select>
      </div>
    </div>
    <div v-if="type !== 'combo'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-semibold">{{ $t('product.supplier') }} *</label>
        <select v-model="form.supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">{{ $t('common.please_select') }}</option>
          <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
        </select>
      </div>
    </div>
    <!-- Kho hàng -->
    <StockPriceTable
      v-model="form.stock_data"
      :stocks="stocks"
      :is-variable-product="form.type === 'variable'"
      :variant-stock-totals="variantStockTotals"
      @remove-stock="onRemoveStock"
      @open-add-stock-modal="showAddStockModal = true"
    />
    <!-- Mô tả -->
    <div>
      <label class="block font-semibold">{{ $t('product.description') }}</label>
      <textarea v-model="form.description" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm min-h-[100px] focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
    <!-- Ảnh -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Ảnh bìa -->
      <div>
        <label class="block font-semibold mb-1">{{ $t('product.cover_image') }}</label>
        <div class="w-32 h-32 border border-dashed rounded relative flex items-center justify-center overflow-hidden cursor-pointer hover:border-blue-500"
            @click="$refs.coverImageInput.click()">
          <input
            ref="coverImageInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleCoverImage"
          />
          <img v-if="coverImagePreview" :src="coverImagePreview" class="object-cover w-full h-full" />
          <div
            v-else
            class="border-2 border-dashed border-gray-300 w-full h-full flex flex-col items-center justify-center text-gray-400 text-center"
          >
            <PhotoIcon class="w-12 h-12 text-gray-400" />
            <span class="mt-1 text-sm">{{ $t('product.select_file') }}</span>
          </div>
          <button v-if="form.cover_image"
                  @click.stop="removeCoverImage"
                  class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow hover:bg-red-600">
            <XMarkIcon class="w-6 h-6 text-white-500" />
          </button>
        </div>
      </div>

      <!-- Ảnh chính (Gallery) -->
      <div>
        <label class="block font-semibold mb-1">{{ $t('product.gallery_images') }}</label>
        <div class="flex flex-wrap gap-2">
          <div v-for="(src, index) in galleryImagePreviews"
              :key="index"
              class="relative w-24 h-24 border rounded overflow-hidden">
            <img :src="src" class="object-cover w-full h-full" />
            <button @click="removeGalleryImage(index)"
                    class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow hover:bg-red-600">
              <XMarkIcon class="w-6 h-6 text-white-500" />
            </button>
          </div>

          <!-- Nút thêm ảnh -->
          <div @click="$refs.galleryImageInput.click()"
              class="w-24 h-24 border-dashed border-2 rounded flex items-center justify-center text-gray-400 cursor-pointer hover:border-blue-500 hover:text-blue-500">
            <PlusIcon class="w-6 h-6 text-blue-500" />
          </div>

          <!-- Input ảnh ẩn -->
          <input
            ref="galleryImageInput"
            type="file"
            accept="image/*"
            multiple
            class="hidden"
            @change="handleGalleryImages"
          />
        </div>
      </div>
    </div>
    <!-- Biến thể -->
    <div v-if="showVariantCheckbox || (mode === 'create' && form.type !== 'combo') || (mode === 'update' && type === 'variable')">
      <label class="inline-flex items-center">
      <input
        type="checkbox"
        v-model="form.has_variant"
        @change="onVariantCheckboxChange"
       class="mr-2"/>
      {{ $t('product.has_variant') }}
      </label>
    </div>
    <!-- Chọn thuộc tính và giá trị con -->
    <div v-if="form.has_variant && variantAttributes.length" class="space-y-4">
      <h2 class="font-semibold text-blue-600">{{ $t('product.msg_max_attr') }}</h2>
      <div v-for="(attr, index) in variantAttributes" :key="attr.id" class="border p-3 rounded shadow-sm">
        <label>
          <input
            type="checkbox"
            :value="attr"
            v-model="selectedAttributes"
            :disabled="(!isAttrSelected(attr) && selectedAttributes.length >= 2)"
            @change="onValueChange($event, attr.id, null)"
          />
          {{ attr.title }}
        </label>
        <div v-if="isAttrSelected(attr)" class="ml-4 mt-2">
          <label v-for="opt in attr.attributes" :key="opt.id" class="inline-flex items-center mr-3">
            <input type="checkbox" :value="Number(opt.id)" v-model="selectedAttributeValues[attr.id]" @change="onValueChange($event, attr.id, opt.id)" />
            <span class="ml-1">{{ opt.title }}</span>
          </label>
        </div>
      </div>
    </div>
    <!-- Lưới Combo -->
    <ComboGrid
     v-if="form.type === 'combo'"
     ref="comboGrid"
     />
    <!-- Lưới biến thể -->
    <VariantGrid
      v-if="form.has_variant && form.category_id"
      :variants="form.variants"
      :stocks="stocks"
      :preview-attributes="previewAttributes"
      :trash-variants="trashVariants"
      @delete-variant="onDeleteVariant"
      @start-restore="startRestore"
      :restoring-variant="restoringVariant"
      @confirm-restore="onRestoreConfirmed"
      @cancel-restore="restoringVariant = null"
      @update:image="onVariantImageChange"
    />
    <!-- Submit -->
    <button @click="handleSubmit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow">
      {{ mode === 'update' ? $t('product.submit_update') : $t('product.submit_create') }}
    </button>
  </div>
      <AddStockModal
      :visible="showAddStockModal"
      :except-ids="stocks.map(s => s.stock_id)"
      @close="showAddStockModal = false"
      @add-stocks="handleAddStocks"
    />
</template>

<script>
import VariantGrid from './VariantGrid.vue'
import ComboGrid from './ComboGrid.vue'
import StockPriceTable from './StockPriceTable.vue'
import Swal from 'sweetalert2'
import AddStockModal from '@/components/common/AddStockModal.vue'
import { XMarkIcon, PlusIcon } from '@heroicons/vue/24/solid'
import { PhotoIcon } from '@heroicons/vue/24/outline'
export default {
  name: 'ProductForm',
  components: { VariantGrid, ComboGrid, StockPriceTable,AddStockModal ,XMarkIcon, PlusIcon, PhotoIcon },
  props: {
    type: { type: String, default: 'single' },
    mode: { type: String, default: 'create' },
    id: { type: [Number, null], default: null },
  },
  computed: {
    variantStockTotals() {
      const totals = {}
      this.stocks.forEach(stock => {
        totals[stock.stock_id] = this.form.variants
          .filter(v => v.stock_id === stock.stock_id)
          .reduce((sum, v) => sum + Number(v.quantity || 0), 0)
      })
      return totals
    },
    showVariantCheckbox() {
      return this.$route.path.includes('/warehouse/product/create/variable')
    },
    coverImagePreview() {
      if (!this.form.cover_image) return null
      return typeof this.form.cover_image === 'string'
        ? this.form.cover_image
        : URL.createObjectURL(this.form.cover_image)
    },
    galleryImagePreviews() {
      return this.form.gallery_images.map(img =>
        img.isOld ? img.url : URL.createObjectURL(img.file)
      )
    },
    hasTrashVariants() {
      return this.trashVariants.length > 0
    }
  },
  data() {
    return {
      form: {
        name: '',
        sku: '',
        barcode: '',
        has_serial: false,
        warranty: '',
        unit_id: '',
        brand_id: '',
        category_id: '',
        supplier_id: '',
        description: '',
        has_variant: false,
        type: 'single',
        variants: [],
        stock_data: [],
        cover_image: null,
        gallery_images: [],
        deleted_gallery_ids : []
      },
      units: [],
      brands: [],
      categories: [],
      suppliers: [],
      stocks: [],
      variantAttributes: [],
      selectedAttributes: [],
      selectedAttributeValues: {},
      previewAttributes: [],
      isMappingVariantData: true,
      trashVariants: [],
      restoringVariant: null,
      hasVariantInitial: false,
      showAddStockModal: false,
      remove_stock_ids: []
    }
  },
  watch: {
    selectedAttributes: {
      handler() {
        if (this.isMappingVariantData) return
        this.trashVariants = []
        this.generateVariantGrid()
      },
      deep: true
    },
    selectedAttributeValues: {
      handler() {
        if (this.isMappingVariantData) return
        this.trashVariants = []
        this.generateVariantGrid()
      },
      deep: true
    }
  },
  async mounted() {
    this.form.type = this.type
    const promises = [this.loadInitialData()]
    if (this.mode === 'update' && this.id) {
      promises.push(this.loadProduct())
    }
    await Promise.all(promises)
  },
  methods: {
    onRemoveStock(stockId) {
      const stock = this.form.stock_data[stockId]
      if (stock?.is_default === 1) return

      if (!this.remove_stock_ids.includes(stockId)) {
        this.remove_stock_ids.push(stockId)
      }

      this.stocks = this.stocks.filter(s => s.stock_id !== stockId)
      delete this.form.stock_data[stockId]
      this.form.variants = this.form.variants.filter(v => v.stock_id !== stockId)
      this.trashVariants = this.trashVariants.filter(v => v.stock_id !== stockId)
    },
    handleAddStocks(newStocks) {
      const tempStocks = [...this.stocks]
      const tempStockData = { ...this.form.stock_data }

      newStocks.forEach(stock => {
        const stockId = stock.stock_id
        const exists = tempStocks.find(s => s.stock_id === stockId)
        if (!exists) {
          tempStocks.push({
            id: stockId,
            stock_id: stockId,
            name: stock.name,
            is_default: stock.is_default || 0
          })

          tempStockData[stockId] = {
            id: null,
            stock_id: stockId,
            quantity: 0,
            purchase_price: 0,
            sell_price: 0,
            max_discount_percent: 0,
            max_increase_percent: 0,
            auto_calc: false,
            name: stock.name,
            is_default: stock.is_default || 0
          }
          // Nếu stock_id nằm trong danh sách remove thì xóa ra
          const idx = this.remove_stock_ids.indexOf(stockId)
          if (idx !== -1) {
            this.remove_stock_ids.splice(idx, 1)
          }
        }
      })

      // Dự đoán số variant sau khi thêm kho
      const selected = this.selectedAttributes
        .filter(attr => {
          const values = this.selectedAttributeValues[attr.id]
          return attr && attr.id && Array.isArray(values) && values.length > 0
        })
        .map(attr => ({
          attr,
          values: this.selectedAttributeValues[attr.id]
        }))

      let count = 0
      if (selected.length > 0) {
        const combinations = this.generateCombinations(
          selected.map(item => ({
            attribute: item.attr,
            values: item.values
          }))
        )
        count = combinations.length * tempStocks.length
      }

      const total = count + this.trashVariants.length
      if (total > 100) {
        Swal.fire({
          icon: 'error',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })
        return
      }

      // Nếu không vượt giới hạn thì apply
      this.stocks = tempStocks
      this.form.stock_data = tempStockData

      this.$nextTick(() => {
        this.generateVariantGrid()
      })
    },
    onVariantImageChange({ index, file }) {
      if (this.form.variants[index]) {
        this.form.variants[index].image = file
      }
    },
    onRestoreConfirmed(variant) {
      // Tìm lại biến thể đã xóa trong trashVariants
      const original = this.trashVariants.find(v =>
        v.stock_id === variant.stock_id &&
        this.isSameAttributes(v.attributes, variant.attributes)
      )

      // Nếu có dữ liệu gốc thì dùng lại toàn bộ thông tin cũ
      const restored = original
        ? { ...original }
        : {
            ...variant,
            quantity: 0,
            sell_price: 0,
            purchase_price: 0,
            sku: '',
            barcode: '',
            image: null,
            is_sale: 1
          }

      this.form.variants.unshift(restored)

      if (original) {
        const index = this.trashVariants.indexOf(original)
        this.trashVariants.splice(index, 1)
      }

      this.restoringVariant = null
    },
    startRestore() {
      this.restoringVariant = {
        stock_id: '',
        attributes: [],
        quantity: 0,
        sell_price: 0,
        purchase_price: 0,
        sku: '',
        barcode: '',
        image: null,
        is_sale: 1,
      }
    },
    onDeleteVariant(index) {
        const variant = this.form.variants[index]
        this.trashVariants.push(variant)
        this.form.variants.splice(index, 1)
    },
    isSameAttributes(a, b) {
      if (a.length !== b.length) return false
      const key = v => `${v.attribute.id}-${v.value.id}`
      const aKeys = a.map(key).sort()
      const bKeys = b.map(key).sort()
      return JSON.stringify(aKeys) === JSON.stringify(bKeys)
    },
    onCategoryChange() {
      this.isMappingVariantData = this.mode === 'create';
      this.selectedAttributes = []
      this.selectedAttributeValues = {}
      this.previewAttributes = []
      this.form.variants = []
      this.trashVariants = []
      this.checkAndLoadVariants()
    },
    async onVariantCheckboxChange(e) {
      const willUncheck = !e.target.checked

      if (this.mode === 'update' && this.hasVariantInitial && willUncheck) {
        const result = await Swal.fire({
          title: 'Bỏ biến thể?',
          text: 'Thao tác này sẽ xóa toàn bộ lưới biến thể đang có. Bạn có chắc chắn không?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Có, tôi chắc chắn',
          cancelButtonText: 'Hủy',
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6'
        })

        if (!result.isConfirmed) {
          this.form.has_variant = true
          return
        }
      }
      this.onToggleVariantLogic()
    },
    onToggleVariantLogic() {
      this.isMappingVariantData = this.mode === 'create';
      this.form.type = this.form.has_variant ? 'variable' : 'single'
      this.selectedAttributes = []
      this.selectedAttributeValues = {}
      this.previewAttributes = []
      this.form.variants = []
      this.trashVariants = []
      this.checkAndLoadVariants()
    },
    onValueChange(event, attrId, valueId) {
      const isChecked = event.target.checked

      // Clone tạm selected values để tính trước
      const tempSelectedAttributeValues = JSON.parse(JSON.stringify(this.selectedAttributeValues))
      const tempSelectedAttributes = [...this.selectedAttributes]

      if (!isChecked && (!valueId || valueId)) {
        if (!valueId) {
          tempSelectedAttributeValues[attrId] = []
          const index = tempSelectedAttributes.findIndex(a => a.id === attrId)
          if (index !== -1) tempSelectedAttributes.splice(index, 1)
        } else {
          tempSelectedAttributeValues[attrId] = tempSelectedAttributeValues[attrId].filter(v => v !== valueId)
        }
      } else {
        // isChecked
        if (!tempSelectedAttributeValues[attrId].includes(valueId)) {
          tempSelectedAttributeValues[attrId].push(valueId)
        }
      }

      // Tính tổ hợp mới nếu thêm value này
      const selected = tempSelectedAttributes
        .filter(attr => {
          const values = tempSelectedAttributeValues[attr.id]
          return attr && attr.id && Array.isArray(values) && values.length > 0
        })
        .map(attr => ({
          attr,
          values: tempSelectedAttributeValues[attr.id]
        }))

      let projected = 0
      if (selected.length > 0) {
        const combinations = this.generateCombinations(
          selected.map(item => ({
            attribute: item.attr,
            values: item.values
          }))
        )
        projected = combinations.length * this.stocks.length
      }

      const total = projected + this.trashVariants.length
      if (total > 100) {
        Swal.fire({
          icon: 'error',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })

        // Rollback checkbox lại thủ công vì Vue đã cập nhật trước
        if (!isChecked) {
          // đang uncheck → rollback lại check
          if (!valueId) {
            const attr = this.variantAttributes.find(a => a.id === attrId)
            if (attr && !this.selectedAttributes.some(a => a.id === attr.id)) {
              this.selectedAttributes.push(attr)
            }
          } else {
            if (!this.selectedAttributeValues[attrId].includes(valueId)) {
              this.selectedAttributeValues[attrId].push(valueId)
            }
          }
        } else {
          // đang check → rollback lại uncheck
          if (!valueId) {
            this.selectedAttributes = this.selectedAttributes.filter(a => a.id !== attrId)
          } else {
            this.selectedAttributeValues[attrId] = this.selectedAttributeValues[attrId].filter(v => v !== valueId)
          }
        }

        return
      }

      if (!isChecked && (!valueId || valueId)) {
        if (!valueId) {
          this.trashVariants = this.trashVariants.filter(variant => {
            return variant.attributes.every(attr => attr.attribute.id !== attrId)
          })
          this.selectedAttributeValues[attrId] = []
          this.selectedAttributes = this.selectedAttributes.filter(a => a.id !== attrId)
        } else {
          this.trashVariants = this.trashVariants.filter(variant => {
            return variant.attributes.every(attr => attr.value.id !== valueId)
          })
          this.selectedAttributeValues[attrId] = this.selectedAttributeValues[attrId].filter(v => v !== valueId)
        }
      } else {
        this.trashVariants = this.trashVariants.filter(variant => {
          return variant.attributes.every(attr => {
            const allowedValues = this.selectedAttributeValues[attr.attribute.id] || []
            return allowedValues.includes(attr.value.id)
          })
        })
      }
      this.$nextTick(() => {
        this.generateVariantGrid()
      })
    },
    async checkAndLoadVariants(isMappingData = false) {
      if (this.form.has_variant && this.form.category_id) {
        const res = await fetch(`/api/warehouse/category/${this.form.category_id}/attributes`)
        const dataJson = await res.json()
        this.variantAttributes = dataJson.data
        if (!isMappingData) {
          this.selectedAttributes = []
          this.selectedAttributeValues = {}
          this.variantAttributes.forEach(attr => {
            this.selectedAttributeValues[attr.id] = []
          })
        }
      } else {
        this.variantAttributes = []
        this.selectedAttributes = []
        this.selectedAttributeValues = {}
      }
    },
    generateVariantGrid() {
      const selected = this.selectedAttributes
        .filter(attr => {
          const values = this.selectedAttributeValues[attr.id]
          return attr && attr.id && Array.isArray(values) && values.length > 0
        })
        .map(attr => ({
          attr,
          values: this.selectedAttributeValues[attr.id]
        }))

      if (selected.length === 0) {
        this.form.variants = []
        this.previewAttributes = []
        return
      }

      this.previewAttributes = selected.map(item => item.attr.title)
      const combinations = this.generateCombinations(
        selected.map(item => ({
          attribute: item.attr,
          values: item.values
        }))
      )

      const newVariants = []

      combinations.forEach(combo => {
        this.stocks.forEach(stock => {
          // tìm variant cũ trùng attributes + stock_id
          const old = this.form.variants.find(v =>
            v.stock_id === stock.stock_id &&
            this.isSameAttributes(v.attributes, combo)
          )

          const isTrashed = this.trashVariants.some(tv =>
            tv.stock_id === stock.stock_id &&
            this.isSameAttributes(tv.attributes, combo)
          )
          if (!isTrashed) {
            newVariants.push({
              id: old?.id ?? null,
              stock_id: stock.stock_id,
              attributes: combo,
              quantity: old?.quantity || 0,
              sell_price: old?.sell_price || 0,
              purchase_price: old?.purchase_price || 0,
              barcode: old?.barcode || '',
              sku: old?.sku || '',
              image: old?.image || null,
              is_sale: old?.is_sale ?? 1,
            })
          }
        })
      })

      this.form.variants = newVariants
    },
    generateCombinations(attributeValueSets) {
      if (!attributeValueSets.length) return []
      const result = []

      const helper = (index, current) => {
        const currentSet = attributeValueSets[index]
        for (const valueId of currentSet.values) {
          const valueObj = currentSet.attribute.attributes.find(attr => attr.id === valueId)
          if (!valueObj) continue

          const next = [...current, {
            attribute: {
              id: currentSet.attribute.id,
              title: currentSet.attribute.title
            },
            value: {
              id: valueObj.id,
              title: valueObj.title,
              variant_id: currentSet.attribute.id
            }
          }]
          if (index === attributeValueSets.length - 1) {
            result.push(next)
          } else {
            helper(index + 1, next)
          }
        }
      }

      helper(0, [])
      return result
    },
    isAttrSelected(attr) {
      return this.selectedAttributes.some(a => a.id === attr.id)
    },
    async loadInitialData() {
      try {
        const headers = { Accept: 'application/json' }
        const fetches = [
          fetch('/api/warehouse/unit/list', { headers }),
          fetch('/api/warehouse/brand/list', { headers }),
          fetch('/api/warehouse/category/list', { headers }),
        ]
        const isCreate = this.mode === 'create'
        const isNotCombo = this.form.type !== 'combo'
        if (isCreate) {
          fetches.push(fetch('/api/warehouse/stock/list?is_default=1', { headers }))
        }
        if (isNotCombo) {
          fetches.push(fetch('/api/supplier/list', { headers }))
        }
        const responses = await Promise.all(fetches)
        const jsons = await Promise.all(responses.map(async (res, i) => {
          if (!res.ok) {
            throw new Error(`Request ${i} failed with status ${res.status}`)
          }
          return await res.json()
        }))
        this.units = jsons[0]?.data.data || []
        this.brands = jsons[1]?.data.data || []
        this.categories = jsons[2]?.data.data || []
        if(isCreate){
          const defaultStocks = jsons[3]?.data.data || []
          this.stocks = defaultStocks.map(stock => ({
            id: stock.id,
            stock_id: stock.id,
            name: stock.name,
            is_default: stock.is_default || 0
          }))
          defaultStocks.forEach(stock => {
            this.form.stock_data[stock.id] = {
              id: stock.id,
              stock_id: stock.id,
              quantity: 0,
              purchase_price: 0,
              sell_price: 0,
              max_discount_percent: 0,
              max_increase_percent: 0,
              auto_calc: false,
              name: stock.name,
              is_default: stock.is_default || 0
            }
          })
        }
        this.suppliers = isNotCombo
          ? (isCreate ? (jsons[4]?.data.data || []) : (jsons[3]?.data.data || []))
          : []
      } catch (err) {
        console.error('Lỗi khi load dữ liệu:', err)
      }
    },
    async loadProduct() {

        const res = await fetch(`/api/warehouse/product/detail/${this.id}`)
        let dataJson = await res.json()
        const data = dataJson.data

        const product = data.product

        Object.assign(this.form, {
          name: product.name,
          sku: product.sku,
          barcode: product.barcode,
          has_serial: Boolean(product.has_serial),
          warranty: product.warranty,
          unit_id: product.unit_id,
          brand_id: product.brand_id,
          category_id: product.category_id,
          supplier_id: product.supplier_id,
          description: product.description || '',
          type: product.type,
          has_variant: Boolean(product.has_variant),
        })

        this.hasVariantInitial = this.form.has_variant

        if (product.image_cover_url) {
          this.form.cover_image = product.image_cover_url
        }
        if (Array.isArray(product.gallery_images)) {
          this.form.gallery_images = product.gallery_images.map(img => ({
            id: img.id,
            url: img.image_url,
            isOld: true
          }))
        }
        const stockDataObj = {}
        product.stock_data.forEach(item => {
          stockDataObj[item.stock_id] = {
            id: item.id,
            stock_id: item.stock_id,
            quantity: item.quantity ?? 0,
            purchase_price: item.purchase_price ?? 0,
            sell_price: item.sell_price ?? 0,
            max_discount_percent: item.max_discount_percent ?? 0,
            max_increase_percent: item.max_increase_percent ?? 0,
            auto_calc: item.auto_calc === 1,
            name: item.stock?.name || '',
            is_default: item.stock?.is_default
          }
        })
        this.form.stock_data = stockDataObj
        this.stocks = Object.keys(stockDataObj).map(stockId => ({
          stock_id: parseInt(stockId),
          name: stockDataObj[stockId].name,
          quantity: stockDataObj[stockId].quantity ?? 0,
          purchase_price: stockDataObj[stockId].purchase_price ?? 0,
          sell_price: stockDataObj[stockId].sell_price ?? 0,
          max_discount_percent: stockDataObj[stockId].max_discount_percent ?? 0,
          max_increase_percent: stockDataObj[stockId].max_increase_percent ?? 0,
          auto_calc: stockDataObj[stockId].auto_calc === 1,
          name: stockDataObj[stockId].name || '',
          is_default: stockDataObj[stockId].is_default
        }))
        //
        if(this.type === 'variable'){
          const variants = []
          const trash = []

          data.stock_products.forEach(v => {
            const variant = {
              id: v.id,
              stock_id: v.stock_id,
              quantity: v.quantity,
              sell_price: v.sell_price,
              purchase_price: v.purchase_price,
              sku: v.sku || '',
              barcode: v.barcode || '',
              is_sale: v.is_sale,
              image: v.image_url || null,
              attributes: v.attributes.map(attr => ({
                attribute: {
                  id: attr.value.variant_id,
                  title: attr.attribute?.title || '',
                },
                value: {
                  id: attr.value.id,
                  title: attr.value.title,
                  variant_id: attr.value.variant_id,
                }
              }))
            }

            if (v.is_using === 1) {
              variants.push(variant)
            } else {
              trash.push(variant)
            }
          })

          this.form.variants = variants
          this.trashVariants = trash
        }
        //
        if(this.type === 'combo'){
           this.$refs.comboGrid.comboItems = data.combo;
        }
        //
        if (this.form.has_variant) {
          this.isMappingVariantData = true
          await this.checkAndLoadVariants(true)

          this.selectedAttributes = []
          this.selectedAttributeValues = {}
          // Khởi tạo giá trị rỗng cho từng attribute
          this.variantAttributes.forEach(attr => {
            this.selectedAttributeValues[attr.id] = []
          })
          product.attributes.forEach(attr => {
            const variantIds = [
              attr.attribute_first?.variant_id,
              attr.attribute_second?.variant_id
            ].filter(Boolean)

            variantIds.forEach(variantId => {
              const foundAttr = this.variantAttributes.find(v => v.id === variantId)
              if (!foundAttr) return

              if (!this.selectedAttributes.some(a => a.id === foundAttr.id)) {
                this.selectedAttributes.push(foundAttr)
              }

              if (!this.selectedAttributeValues[foundAttr.id]) {
                this.$set(this.selectedAttributeValues, foundAttr.id, [])
              }

              const idsToAdd = [attr.attribute_first?.id, attr.attribute_second?.id].filter(Boolean)
              idsToAdd.forEach(id => {
                id = Number(id)
                // Chỉ thêm nếu giá trị này vẫn còn trong variantAttributes hiện tại
                const exist = foundAttr.attributes.some(opt => opt.id === id)
                if (exist && !this.selectedAttributeValues[foundAttr.id].includes(id)) {
                  this.selectedAttributeValues[foundAttr.id].push(id)
                }
              })
            })
          })

          this.previewAttributes = this.selectedAttributes.map(a => a.title)
        }

    },
    handleCoverImage(e) {
      const file = e.target.files[0]
      this.form.cover_image = file
    },
    removeCoverImage() {
      this.form.cover_image = null
    },
    handleGalleryImages(e) {
      const newFiles = Array.from(e.target.files).map(file => ({
        file,
        isOld: false
      }))
      this.form.gallery_images.push(...newFiles)
    },
    removeGalleryImage(index) {
      const img = this.form.gallery_images[index]
      if (img && img.isOld && img.id) {
        if (!Array.isArray(this.form.deleted_gallery_ids)) {
          this.form.deleted_gallery_ids = []
        }
        this.form.deleted_gallery_ids.push(img.id)
      }
      this.form.gallery_images.splice(index, 1)
    },
    async handleSubmit() {
      try {
        const formData = new FormData()
        Object.entries(this.form).forEach(([key, value]) => {
          if (['cover_image', 'gallery_images', 'variants', 'stock_data'].includes(key)) return
          if (typeof value === 'object' && value !== null) {
            formData.append(key, JSON.stringify(value))
          } else {
            formData.append(key, value ?? '')
          }
        })
        const finalStockData = []
        if (this.mode === 'update') {
          Object.entries(this.form.stock_data).forEach(([stockId, row]) => {
            finalStockData.push({
              id:row.id??null,
              stock_id: parseInt(stockId),
              quantity: row.quantity ?? 0,
              purchase_price: row.purchase_price ?? 0,
              sell_price: row.sell_price ?? 0,
              max_discount_percent: row.max_discount_percent ?? 0,
              max_increase_percent: row.max_increase_percent ?? 0,
              auto_calc: row.auto_calc ? 1 : 0
            })
          })
          if(this.remove_stock_ids.length > 0){
              this.remove_stock_ids.forEach(id => {
                formData.append('remove_stock_ids[]', id)
              })
          }
        } else {
          Object.values(this.form.stock_data).forEach(row => {
            finalStockData.push({
              stock_id: row.stock_id,
              quantity: row.quantity ?? 0,
              purchase_price: row.purchase_price ?? 0,
              sell_price: row.sell_price ?? 0,
              max_discount_percent: row.max_discount_percent ?? 0,
              max_increase_percent: row.max_increase_percent ?? 0,
              auto_calc: row.auto_calc ? 1 : 0
            })
          })
        }
        formData.append('stock_data', JSON.stringify(finalStockData))
        formData.append('has_serial', this.form.has_serial ? 1 : 0)
        formData.append('has_variant', this.form.has_variant ? 1 : 0)

        if (this.form.cover_image instanceof File) {
          formData.append('cover_image', this.form.cover_image)
        }
        this.form.gallery_images.forEach((img, index) => {
          if (!img.isOld && img.file instanceof File) {
            formData.append('gallery_images[]', img.file)
          }
        })
        if (this.form.deleted_gallery_ids?.length) {
          this.form.deleted_gallery_ids.forEach((id, i) => {
            formData.append(`deleted_gallery_ids[]`, id)
          })
        }
        const allVariants = [...this.form.variants, ...this.trashVariants]

        allVariants.forEach((variant, i) => {
          if (variant.id) {
            formData.append(`variants[${i}][id]`, variant.id)
          }
          formData.append(`variants[${i}][stock_id]`, variant.stock_id)
          formData.append(`variants[${i}][quantity]`, variant.quantity)
          formData.append(`variants[${i}][sell_price]`, variant.sell_price)
          formData.append(`variants[${i}][purchase_price]`, variant.purchase_price)
          formData.append(`variants[${i}][sku]`, variant.sku || '')
          formData.append(`variants[${i}][barcode]`, variant.barcode || '')
          formData.append(`variants[${i}][is_sale]`, variant.is_sale)

          const isUsing = this.trashVariants.includes(variant) ? 0 : 1
          formData.append(`variants[${i}][is_using]`, isUsing)

          if (variant.image instanceof File) {
            formData.append(`variants[${i}][image]`, variant.image)
          }

          variant.attributes.forEach((attr, j) => {
            formData.append(`variants[${i}][attributes][${j}][attribute_id]`, attr.attribute.id)
            formData.append(`variants[${i}][attributes][${j}][value_id]`, attr.value.id)
          })
        })

        if (this.form.type === 'combo') {
          formData.append('combo', JSON.stringify(this.$refs.comboGrid.comboItems))
        }

        const url = this.mode === 'update'
          ? `/api/warehouse/product/update/${this.id}`
          : '/api/warehouse/product/create'
        if (this.mode === 'update') {
          formData.append('_method', 'PUT')
        }

        const res = await window.axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

      await Swal.fire({
        icon: 'success',
        title: 'Thành công!',
        showConfirmButton: false,
        timer: 1500
      })
      this.$router.push('/warehouse/product')
      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: 'Thất bại khi gửi form.',
          text: err?.response?.data?.message || 'Đã xảy ra lỗi. Vui lòng thử lại.'
        })
      }
    },
  }
}
</script>
