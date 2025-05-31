<template>
  <div class="space-y-6 p-4 bg-white rounded-xl shadow-md">
    <h1 class="text-2xl font-bold">
      {{ mode === 'update' ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới' }}
    </h1>
    <!-- Thông tin chung -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">Tên sản phẩm *</label>
        <input v-model="form.name" type="text" placeholder="Vui lòng nhập" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label class="block font-semibold">SKU</label>
        <input v-model="form.sku" type="text" placeholder="Vui lòng nhập" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label class="block font-semibold">Mã vạch / Barcode</label>
        <input v-model="form.barcode" type="text" placeholder="Vui lòng nhập" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
    </div>
    <div>
      <label class="inline-flex items-center">
        <input type="checkbox" v-model="form.has_serial" class="mr-2" />
        Sản phẩm có mã serial Serial/IMEL
      </label>
    </div>
    <div>
      <label class="block font-semibold">Hình thức bảo hành</label>
      <select
        v-model="form.warranty"
        class="w-full mt-2 px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">-- Chọn bảo hành --</option>
        <option value="6_months">6 tháng</option>
        <option value="12_months">12 tháng</option>
        <option value="1_year">1 năm</option>
        <option value="5_years">5 năm</option>
        <option value="10_years">10 năm</option>
        <option value="21_years">21 năm</option>
      </select>
    </div>
    <!-- Phân loại -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">Đơn vị</label>
        <select v-model="form.unit_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">Vui lòng chọn</option>
          <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold">Nhãn hiệu</label>
        <select v-model="form.brand_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">Vui lòng chọn</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
        </select>
      </div>
      <div>
        <label class="block font-semibold">Danh mục</label>
        <select @change="onCategoryChange" v-model="form.category_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">Vui lòng chọn</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
        </select>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-semibold">Nhà cung cấp *</label>
        <select v-model="form.supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
          <option value="">Vui lòng chọn</option>
          <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
        </select>
      </div>
    </div>
    <!-- Kho hàng -->
    <StockPriceTable
      v-model="form.stock_data"
      :stocks="stocks"
    />
    <!-- Mô tả -->
    <div>
      <label class="block font-semibold">Mô tả</label>
      <textarea v-model="form.description" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm min-h-[100px] focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
    <!-- Ảnh -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-semibold">Ảnh bìa</label>
        <input type="file" @change="handleCoverImage" accept="image/*" class="w-full border border-gray-300 px-4 py-2 rounded" />
        <div v-if="form.cover_image" class="mt-2 relative w-max">
          <img v-if="coverImagePreview" :src="coverImagePreview" class="w-24 h-24 object-cover rounded border" />
          <button @click="removeCoverImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full px-2 py-1 text-xs">×</button>
        </div>
      </div>
      <div>
        <label class="block font-semibold">Ảnh chính</label>
        <input type="file" multiple @change="handleGalleryImages" accept="image/*" class="w-full border border-gray-300 px-4 py-2 rounded" />
        <div class="flex flex-wrap gap-2 mt-2">
          <div v-for="(src, index) in galleryImagePreviews" :key="index" class="relative w-max">
            <img :src="src" alt="Gallery" class="w-20 h-20 object-cover rounded border" />
            <button @click="removeGalleryImage(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full px-2 py-1 text-xs">×</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Biến thể -->
    <div v-if="showVariantCheckbox || form.type !== 'combo'">
      <label class="inline-flex items-center">
        <input
          type="checkbox"
          v-model="form.has_variant"
          class="mr-2"
          @change="onToggleVariant"
        />
        Sản phẩm có biến thể
      </label>
    </div>
    <!-- Chọn thuộc tính và giá trị con -->
    <div v-if="form.has_variant && variantAttributes.length" class="space-y-4">
      <h2 class="font-semibold text-blue-600">Chọn thuộc tính biến thể (tối đa 2)</h2>
      <div v-for="(attr, index) in variantAttributes" :key="attr.id" class="border p-3 rounded shadow-sm">
        <label>
          <input
            type="checkbox"
            :value="attr"
            v-model="selectedAttributes"
            :disabled="(!isAttrSelected(attr) && selectedAttributes.length >= 2)"
            @change="onAttributeChange"
          />
          {{ attr.title }}
        </label>
        <div v-if="isAttrSelected(attr)" class="ml-4 mt-2">
          <label v-for="opt in attr.attributes" :key="opt.id" class="inline-flex items-center mr-3">
            <input type="checkbox" :value="Number(opt.id)" v-model="selectedAttributeValues[attr.id]" @change="onAttributeChange" />
            <span class="ml-1">{{ opt.title }}</span>
          </label>
        </div>
      </div>
    </div>
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
    />
    <!-- Submit -->
    <button @click="handleSubmit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow">
      {{ mode === 'update' ? 'Cập nhật' : 'Tạo mới' }}
    </button>
  </div>
</template>

<script>
import VariantGrid from './VariantGrid.vue'
import StockPriceTable from './StockPriceTable.vue'

export default {
  name: 'ProductForm',
  components: { VariantGrid, StockPriceTable },
  props: {
    type: { type: String, default: 'single' },
    mode: { type: String, default: 'create' },
    id: { type: [Number, null], default: null },
  },
  computed: {
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
      return this.form.gallery_images.map(file =>
        typeof file === 'string' ? file : URL.createObjectURL(file)
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
    }
  },
  watch: {
    selectedAttributes: {
      handler() {
        if (this.isMappingVariantData) return
        this.generateVariantGrid()
      },
      deep: true
    },
    selectedAttributeValues: {
      handler() {
        if (this.isMappingVariantData) return
        this.generateVariantGrid()
      },
      deep: true
    }
  },
  async mounted() {
    this.form.type = this.type
    await this.loadInitialData()
    if (this.mode === 'update' && this.id) await this.loadProduct()
  },
  methods: {
    onRestoreConfirmed(variant) {
      this.form.variants.unshift(variant)

      const index = this.trashVariants.findIndex(v =>
        v.stock_id === variant.stock_id &&
        this.isSameAttributes(v.attributes, variant.attributes)
      )
      if (index !== -1) {
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
      this.isMappingVariantData = false
      this.selectedAttributes = []
      this.selectedAttributeValues = {}
      this.previewAttributes = []
      this.form.variants = []
      this.trashVariants = []
      this.checkAndLoadVariants()
    },
    onToggleVariant() {
      this.isMappingVariantData = false
      this.form.type = this.form.has_variant ? 'variable' : 'single'
      this.selectedAttributes = []
      this.selectedAttributeValues = {}
      this.previewAttributes = []
      this.form.variants = []
      this.trashVariants = []
      this.checkAndLoadVariants()
    },
    onAttributeChange() {
      this.isMappingVariantData = false
      this.$nextTick(() => {
        // Lọc lại trashVariants sau khi checkbox cập nhật xong
        this.trashVariants = this.trashVariants.filter(variant => {
          return variant.attributes.every(attr => {
            const allowedValues = this.selectedAttributeValues[attr.attribute.id] || []
            return allowedValues.includes(attr.value.id)
          })
        })
        // Cập nhật lại grid
        this.generateVariantGrid()
      })
    },
    async checkAndLoadVariants(isMappingData = false) {
      if (this.form.has_variant && this.form.category_id) {
        const res = await fetch(`/api/warehouse/category/${this.form.category_id}/attributes`)
        this.variantAttributes = await res.json()
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
            v.stock_id === stock.id &&
            this.isSameAttributes(v.attributes, combo)
          )

          const isTrashed = this.trashVariants.some(tv =>
            tv.stock_id === stock.id &&
            this.isSameAttributes(tv.attributes, combo)
          )
          if (!isTrashed) {
            newVariants.push({
              stock_id: stock.id,
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
        const [unitRes, brandRes, categoryRes, supplierRes, stockRes] = await Promise.all([
          fetch('/api/warehouse/unit/list', { headers }),
          fetch('/api/warehouse/brand/list', { headers }),
          fetch('/api/warehouse/category/list', { headers }),
          fetch('/api/supplier/list', { headers }),
          fetch('/api/warehouse/stock/list', { headers })
        ])
        const [units, brands, categories, suppliers, stocks] = await Promise.all([
          unitRes.json(),
          brandRes.json(),
          categoryRes.json(),
          supplierRes.json(),
          stockRes.json()
        ])
        this.units = units.data
        this.brands = brands.data
        this.categories = categories.data
        this.suppliers = suppliers.data
        this.stocks = stocks.data
      } catch (err) {
        console.error('Lỗi khi load dữ liệu:', err)
      }
    },
    async loadProduct() {
      try {
        const res = await fetch(`/api/warehouse/product/detail/${this.id}`)
        const data = await res.json()
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
          has_variant: Boolean(product.have_variant),
        })

        if (product.image_cover_url) {
          this.form.cover_image = product.image_cover_url
        }
        if (Array.isArray(product.gallery_images)) {
          this.form.gallery_images = product.gallery_images.map(img => img.image_url)
        }
        this.form.stock_data = product.stock_data || []
        //
        const variants = []
        const trash = []

        data.stock_products.forEach(v => {
          const variant = {
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
        //
        if (this.form.has_variant && product.attributes?.length) {
          this.isMappingVariantData = true
          await this.checkAndLoadVariants(true)

          this.selectedAttributes = []
          this.selectedAttributeValues = {}

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
                this.selectedAttributeValues[foundAttr.id] = []
              }

              const idsToAdd = [attr.attribute_first?.id, attr.attribute_second?.id].filter(Boolean)
              idsToAdd.forEach(id => {
                id = Number(id)
                if (!this.selectedAttributeValues[foundAttr.id].includes(id)) {
                  this.selectedAttributeValues[foundAttr.id].push(id)
                }
              })
            })
          })

          this.previewAttributes = this.selectedAttributes.map(a => a.title)
        }
      } catch (err) {
        console.error('Lỗi khi load product:', err)
        alert('❌ Không thể tải dữ liệu sản phẩm')
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
      this.form.gallery_images = [...e.target.files]
    },
    removeGalleryImage(index) {
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

        formData.append('stock_data', JSON.stringify(this.form.stock_data))

        if (this.form.cover_image instanceof File) {
          formData.append('cover_image', this.form.cover_image)
        }
        this.form.gallery_images.forEach((file, index) => {
          if (file instanceof File) {
            formData.append(`gallery_images[${index}]`, file)
          }
        })
        const allVariants = [...this.form.variants, ...this.trashVariants]

        allVariants.forEach((variant, i) => {
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

        const url = this.mode === 'update'
          ? `/api/warehouse/product/update/${this.id}`
          : '/api/warehouse/product/create'
        if (this.mode === 'update') {
          formData.append('_method', 'PUT')
        }

        const res = await window.axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        alert('✔️ Thành công!')
        this.$router.push('/warehouse/product')
      } catch (err) {
        console.error(err)
        alert('❌ Thất bại khi gửi form.')
      }
    },
  }
}
</script>
