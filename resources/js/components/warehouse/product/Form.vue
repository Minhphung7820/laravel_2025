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
        <select v-model="form.category_id" class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm">
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
    <div v-if="showVariantCheckbox">
      <label class="inline-flex items-center">
        <input
          type="checkbox"
          v-model="form.has_variant"
          class="mr-2"
          @change="form.type = form.has_variant ? 'variable' : 'single'"
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
            :disabled="!selectedAttributes.includes(attr) && selectedAttributes.length >= 2"
          />
          {{ attr.title }}
        </label>
        <div v-if="selectedAttributes.includes(attr)" class="ml-4 mt-2">
          <label v-for="opt in attr.attributes" :key="opt.id" class="inline-flex items-center mr-3">
            <input type="checkbox" :value="opt" v-model="selectedAttributeValues[attr.id]" />
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
    mode: { type: String, default: 'create' },
    id: { type: [Number, null], default: null },
  },
  computed: {
    showVariantCheckbox() {
      return this.$route.path.includes('/warehouse/product/create/variable')
    },
    coverImagePreview() {
      return this.form.cover_image ? URL.createObjectURL(this.form.cover_image) : null
    },
    galleryImagePreviews() {
      return this.form.gallery_images.map(file => URL.createObjectURL(file))
    },
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
      variantAttributes: [], // từ API
      selectedAttributes: [],
      selectedAttributeValues: {}, // { [attrId]: [giá trị đã chọn] }
      previewAttributes: []
    }
  },
  watch: {
    'form.category_id'(val) {
      this.checkAndLoadVariants()
    },
    'form.has_variant'(val) {
      this.checkAndLoadVariants()
    },
    selectedAttributes: {
      handler() {
        this.generateVariantGrid()
      },
      deep: true
    },
    selectedAttributeValues: {
      handler() {
        this.generateVariantGrid()
      },
      deep: true
    }
  },
  mounted() {
    const path = this.$route.path

    if (path.includes('/warehouse/product/create/combo')) {
      this.form.type = 'combo'
    } else if (path.includes('/warehouse/product/create/variable')) {
      this.form.type = 'single'
    }

    this.loadInitialData()
    if (this.mode === 'update' && this.id) this.loadProduct()
  },
  methods: {
    async checkAndLoadVariants() {
      if (this.form.has_variant && this.form.category_id) {
        const res = await fetch(`/api/warehouse/category/${this.form.category_id}/attributes`)
        this.variantAttributes = await res.json()

        // Khởi tạo cấu trúc chọn giá trị
        this.selectedAttributeValues = {}
        this.variantAttributes.forEach(attr => {
          this.selectedAttributeValues[attr.id] = []
        })
      } else {
        this.variantAttributes = []
        this.selectedAttributes = []
        this.selectedAttributeValues = {}
      }
    },
    generateVariantGrid() {
      const selected = this.selectedAttributes.map(attr => ({
        attr,
        values: this.selectedAttributeValues[attr.id]
      }))

      // Nếu chưa chọn đủ (ít nhất 1 giá trị mỗi thuộc tính), không generate
      if (
        selected.length === 0 ||
        selected.some(item => !item.values || item.values.length === 0)
      ) {
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

      const stockVariants = []

      combinations.forEach(combo => {
        this.stocks.forEach(stock => {
          stockVariants.push({
            stock_id: stock.id,
            attributes: combo,
            quantity: 0,
            sell_price: 0,
            purchase_price: 0,
            barcode: '',
            sku: '',
            image: null,
            is_sale: 1
          })
        })
      })

      this.form.variants = stockVariants
    },
    generateCombinations(attributeValueSets) {
      if (!attributeValueSets.length) return []

      const result = []

      const helper = (index, current) => {
        const currentSet = attributeValueSets[index]
        for (const value of currentSet.values) {
          const next = [...current, { attribute: currentSet.attribute, value }]
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
      // Load dữ liệu sản phẩm theo ID để edit
    },
    handleCoverImage(e) {
      const file = e.target.files[0]
      this.form.cover_image = file

      console.log(this.form.cover_image);

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

        // Xử lý các field thông thường
        Object.entries(this.form).forEach(([key, value]) => {
          if (['cover_image', 'gallery_images', 'variants', 'stock_data'].includes(key)) return

          if (typeof value === 'object' && value !== null) {
            formData.append(key, JSON.stringify(value))
          } else {
            formData.append(key, value ?? '')
          }
        })

        // Xử lý stock_data (đảm bảo là object → stringify)
        const stockData = typeof this.form.stock_data === 'string'
          ? this.form.stock_data
          : JSON.stringify(this.form.stock_data)

        formData.append('stock_data', stockData)

        // Xử lý ảnh bìa
        if (this.form.cover_image instanceof File) {
          formData.append('cover_image', this.form.cover_image)
        }

        // Xử lý ảnh chính
        this.form.gallery_images.forEach((file, index) => {
          if (file instanceof File) {
            formData.append(`gallery_images[${index}]`, file)
          }
        })

        // Xử lý biến thể
        this.form.variants.forEach((variant, i) => {
          formData.append(`variants[${i}][stock_id]`, variant.stock_id)
          formData.append(`variants[${i}][quantity]`, variant.quantity)
          formData.append(`variants[${i}][sell_price]`, variant.sell_price)
          formData.append(`variants[${i}][purchase_price]`, variant.purchase_price)
          formData.append(`variants[${i}][sku]`, variant.sku || '')
          formData.append(`variants[${i}][barcode]`, variant.barcode || '')
          formData.append(`variants[${i}][is_sale]`, variant.is_sale)

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

        // Nếu là update, Laravel có thể yêu cầu method PUT
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
 },
}
</script>
