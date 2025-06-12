<template>
<!-- Loader -->
<div v-if="loading" class="fixed inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">
  <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
  </svg>
</div>
<!-- Loader -->
  <div class="space-y-6 p-4 bg-white rounded-xl shadow-md">
    <h1 class="text-2xl font-bold">
      {{ mode === 'update' ? $t('product.update_title') : $t('product.create_title') }}
    </h1>
    <!-- Thông tin chung -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">{{ $t('product.name') }} <span class="text-red-500">*</span></label>
        <input
          v-model="form.name"
          :class="['w-full px-4 py-2 border rounded shadow-sm focus:outline-none', errors.name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
          type="text"
          :placeholder="$t('common.please_enter')"
          class="is-invalid"
        />
        <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
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
      <label class="block font-semibold">{{ $t('product.supplier') }} <span class="text-red-500">*</span></label>
      <select
        v-model="form.supplier_id"
        :class="['w-full px-4 py-2 border rounded shadow-sm', errors.supplier_id ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500']"
        class="is-invalid"
      >
        <option value="">{{ $t('common.please_select') }}</option>
        <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
      </select>
      <p v-if="errors.supplier_id" class="text-sm text-red-500 mt-1">{{ errors.supplier_id }}</p>
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

    <div v-if="form.has_variant" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
      <div>
        <label class="block font-semibold">Cách tạo biến thể</label>
        <select
          @change="onChangeVariantInputMode"
          v-model="form.variant_input_mode"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm"
        >
          <option value="create">Thêm thủ công (Mặc định)</option>
          <option value="from_category">Lấy từ danh mục</option>
        </select>
      </div>
    </div>

   <div v-if="form.has_variant && form.variant_input_mode === 'create'" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
      <!-- Mỗi thuộc tính -->
      <div
        v-for="(attr, index) in form.custom_attributes"
        :key="attr.id"
        class="bg-gray-100 border border-dashed border-blue-500 rounded-xl shadow-sm p-6 space-y-4"
      >
        <!-- Tên thuộc tính -->
        <div class="relative w-full">
          <label class="block text-lg font-semibold text-gray-700 mb-1">Tên thuộc tính {{index + 1}}</label>

          <!-- Input -->
          <input
            v-model="attr.title"
            maxlength="30"
            @input="validateAttributeTitle(index)"
            placeholder="VD: Màu, Size..."
            :class="[
              'w-full px-4 py-2 border rounded shadow-sm focus:outline-none pr-28 bg-white',
              attributeErrors[`attr_${attr.id}`]
                ? 'border-red-500 focus:ring-red-500'
                : 'border-gray-300 focus:ring-blue-500'
            ]"
          />

          <!-- Char counter -->
          <span
            class="absolute top-11.5 right-10 text-xs"
            :class="attr.title.length > 30 ? 'text-red-500' : 'text-gray-500'"
          >{{ attr.title.length }}/30</span>

          <!-- Nút xoá -->
          <button
            @click="removeCustomAttribute(index)"
            class="cursor-pointer absolute top-10.5 right-2 w-6 h-6 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center"
            title="Xoá thuộc tính"
          ><XMarkIcon class="w-6 h-6 text-white-500" /></button>

          <!-- Lỗi -->
          <p v-if="attributeErrors[`attr_${attr.id}`]" class="text-sm text-red-500 mt-1">
            {{ attributeErrors[`attr_${attr.id}`] }}
          </p>
        </div>


        <!-- Giá trị -->
        <div>
          <label class="block text-lg font-semibold text-gray-700 mb-2">Giá trị</label>
          <div class="grid grid-cols-2 gap-x-4 gap-y-3">
            <div
              v-for="(val, i) in attr.values"
              :key="val.id"
              class="relative"
            >
              <input
                v-model="val.title"
                maxlength="20"
                placeholder="VD: Đỏ, Xanh, L"
                @input="validateAttributeValue(index, i, val.id)"
                :class="[
                  'w-full px-3 py-2 border rounded shadow-sm focus:outline-none pr-28 bg-white',
                  attributeErrors[`val_${val.id}`]
                    ? 'border-red-500 focus:ring-red-500'
                    : 'border-gray-300 focus:ring-blue-500'
                ]"
              />

              <!-- Char counter -->
              <span
                class="absolute top-3.5 right-10 text-xs"
                :class="val.title.length > 20 ? 'text-red-500' : 'text-gray-500'"
              >{{ val.title.length }}/20</span>

              <!-- Nút X xoá -->
              <button
                @click="removeAttributeValue(index, i)"
                class="cursor-pointer absolute top-2.5 right-2 w-6 h-6 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center"
                title="Xoá giá trị"
              ><XMarkIcon class="w-6 h-6 text-white-500" /></button>

              <!-- Lỗi -->
              <p
                v-if="attributeErrors[`val_${val.id}`]"
                class="text-sm text-red-500 mt-1"
              >
                {{ attributeErrors[`val_${val.id}`] }}
              </p>
            </div>
            <!-- Nút thêm giá trị -->
            <div v-if="attr.values.length < 10" class="col-span-2">
              <button
                @click="addAttributeValue(index)"
                :disabled="hasAnyValidationError()"
                class="cursor-pointer w-full border border-dashed border-blue-500 text-blue-500 py-2 rounded bg-blue-50 flex items-center justify-center px-4"
              >
                <span>+ Thêm giá trị ({{ attr.values.length }}/10)</span>
              </button>
            </div>
          </div>
        </div>
      </div>
   </div>
     <!-- Nút thêm thuộc tính -->
   <button
        v-if="form.custom_attributes.length < 2"
        @click="addCustomAttribute"
        :disabled="hasAnyValidationError()"
        class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
    >+ Thêm thuộc tính {{ form.custom_attributes.length + 1 }}</button>
    <!-- Chọn thuộc tính và giá trị con -->
    <div v-if="form.has_variant && form.variant_input_mode === 'from_category'" class="space-y-4">
      <h2 class="font-semibold text-blue-600">{{ $t('product.msg_max_attr') }}</h2>

      <!-- Loading vòng xoay -->
      <div v-if="isLoadingAttributes" class="flex items-center space-x-2 text-gray-500 text-sm">
        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <!-- <span>{{ $t('product.loading_attributes') || 'Đang tải thuộc tính...' }}</span> -->
      </div>

      <!-- Danh sách thuộc tính và value -->
      <div v-else>
        <div v-for="(attr, index) in variantAttributes" :key="attr.id" class="p-3 rounded w-fit">
          <label class="font-semibold block mb-1">
            <input
              type="checkbox"
              :value="attr"
              v-model="selectedAttributes"
              :disabled="(!isAttrSelected(attr) && selectedAttributes.length >= 2)"
              @change="onValueChange($event, attr.id, null)"
            />
            {{ attr.title }}
          </label>
          <div
            v-if="isAttrSelected(attr)"
            class="ml-4 mt-2 inline-flex flex-wrap gap-2 w-auto max-w-full"
          >
            <label
              v-for="opt in attr.attributes"
              :key="opt.id"
              class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded"
            >
              <input
                type="checkbox"
                :value="Number(opt.id)"
                v-model="selectedAttributeValues[attr.id]"
                @change="onValueChange($event, attr.id, opt.id)"
              />
              <span>{{ opt.title }}</span>
            </label>
          </div>
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
      v-if="form.has_variant && (form.variant_input_mode === 'create' || form.variant_input_mode === 'from_category')"
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
      @remove:variant-image="onRemoveVariantImage"
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
import _ from 'lodash'

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
        deleted_gallery_ids : [],
        variant_input_mode: 'create',
        custom_attributes: []
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
      remove_stock_ids: [],
      errors: {},
      loading: true,
      removed_variant_image_ids: [],
      isLoadingAttributes: false,
      attributeErrors: {},
      variant_input_mode_original: null
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
    this.$watch(
    () => JSON.stringify(this.form.custom_attributes),
    () => {
          this.trashVariants = [];
          this.generateVariantGrid();
    },
    { deep: true }
    );
    await Promise.all(promises)
    this.loading = false
  },
  methods: {
    hasAnyValidationError() {
      return Object.keys(this.attributeErrors).length > 0
    },
    validateAttributeTitle(index) {
      const attr = this.form.custom_attributes[index]
      const key = `attr_${attr.id}`
      const title = attr.title.trim()

      const clone = { ...this.attributeErrors }

      if (!title) {
        clone[key] = 'Tên thuộc tính không được để trống'
      } else if (title.length > 30) {
        clone[key] = 'Tên thuộc tính tối đa 30 ký tự'
      } else {
        delete clone[key]
      }

      this.attributeErrors = clone
    },
    validateAttributeValue(attrIndex, valIndex, valId) {
      const val = this.form.custom_attributes[attrIndex].values[valIndex]
      const key = `val_${valId}`
      const title = val.title.trim()

      const clone = { ...this.attributeErrors }

      if (!title) {
        clone[key] = 'Giá trị không được để trống'
      } else if (title.length > 20) {
        clone[key] = 'Giá trị tối đa 20 ký tự'
      } else {
        delete clone[key]
      }

      this.attributeErrors = clone
    },
    addCustomAttribute() {
      if (this.hasAnyValidationError()) {
        Swal.fire({
          icon: 'warning',
          title: 'Vui lòng điền đầy đủ tất cả trước khi thêm mới',
          confirmButtonText: 'OK'
        })
        return
      }

      if (this.form.custom_attributes.length >= 2) return

      const fakeAttrId = `attr#temp-${Date.now()}`
      const fakeValId = `val#temp-${Math.random().toString(36).substring(2, 5)}`
      const simulatedAttrs = JSON.parse(JSON.stringify(this.form.custom_attributes))

      simulatedAttrs.push({
        id: fakeAttrId,
        title: 'Tạm',
        values: [{ id: fakeValId, title: 'x' }]
      })

      const selected = simulatedAttrs
        .filter(attr => attr.title.trim() && attr.values.length > 0)
        .map(attr => ({
          attr: {
            id: attr.id,
            title: attr.title,
            attributes: attr.values
          },
          values: attr.values.map(v => v.id)
        }))

      const combinations = this.generateCombinations(
        selected.map(item => ({
          attribute: item.attr,
          values: item.values
        }))
      )

      const projected = combinations.length * this.stocks.length
      const total = projected + this.trashVariants.length

      if (total > 100) {
        Swal.fire({
          icon: 'error',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })
        return
      }

      const newAttrId = `attr#${Date.now()}-${Math.random().toString(36).substring(2, 5)}`
      this.form.custom_attributes.push({
        id: newAttrId,
        title: '',
        values: []
      })
      this.attributeErrors[`attr_${newAttrId}`] = 'Tên thuộc tính không được để trống'
    },
    removeCustomAttribute(index) {
      const attr = this.form.custom_attributes[index]
      const attrKey = `attr_${attr.id}`

      if (this.attributeErrors[attrKey]) {
        delete this.attributeErrors[attrKey]
      }

      attr.values.forEach(v => {
        const valKey = `val_${v.id}`
        if (this.attributeErrors[valKey]) {
          delete this.attributeErrors[valKey]
        }
      })

      this.form.custom_attributes.splice(index, 1)
    },
    addAttributeValue(attrIndex) {
      if (this.hasAnyValidationError()) {
        Swal.fire({
          icon: 'warning',
          title: 'Vui lòng điền đầy đủ tất cả trước khi thêm mới',
          confirmButtonText: 'OK'
        })
        return
      }

      // Tạo bản sao để mô phỏng thêm giá trị
      const simulatedAttrs = JSON.parse(JSON.stringify(this.form.custom_attributes))
      const attr = simulatedAttrs[attrIndex]
      const newId = `val#${Date.now()}-${Math.random().toString(36).substring(2, 5)}`
      attr.values.push({ id: newId, title: 'Giá trị mới' })

      const selected = simulatedAttrs
        .filter(a => a.title.trim() && a.values.length > 0)
        .map(a => ({
          attr: {
            id: a.id,
            title: a.title,
            attributes: a.values
          },
          values: a.values.map(v => v.id)
        }))

      const combinations = this.generateCombinations(
        selected.map(item => ({
          attribute: item.attr,
          values: item.values
        }))
      )

      const projected = combinations.length * this.stocks.length
      const total = projected + this.trashVariants.length

      if (total > 100) {
        Swal.fire({
          icon: 'error',
          title: this.$t('variant_grid.limit_exceeded'),
          text: this.$t('variant_grid.limit_exceeded_msg'),
          confirmButtonText: 'OK'
        })
        return
      }

      this.form.custom_attributes[attrIndex].values.push({ id: newId, title: '' })
      this.attributeErrors[`val_${newId}`] = 'Giá trị không được để trống'
    },
    removeAttributeValue(attrIndex, valueIndex) {
      this.form.custom_attributes[attrIndex].values.splice(valueIndex, 1)
    },
    resetCacheableListByKey(){
      const allKeys = this.$store.getters['cache/getAllCacheKeys']('product')
      const listKeys = allKeys.filter(key =>
        key.includes('page') &&
        key.includes('filters') &&
        !key.includes('/create') &&
        !key.startsWith('edit-')
      )
      listKeys.forEach(key => {
        this.$store.dispatch('cache/clearCacheKey', {
          module: 'product',
          key
        })
      })
    },
    onRemoveVariantImage(variantId) {
      if (!this.removed_variant_image_ids.includes(variantId)) {
        this.removed_variant_image_ids.push(variantId)
      }
    },
    validateForm() {
      this.errors = {}

      if (!this.form.name) {
        this.errors.name = this.$t('product.required_name')
      }

      if (!this.form.supplier_id && (this.form.type === 'single' || this.form.type === 'variable')) {
        this.errors.supplier_id = this.$t('product.required_supplier')
      }

      if (this.form.has_variant && Object.keys(this.attributeErrors).length > 0) {
        this.$nextTick(() => {
          const firstError = document.querySelector('.border-red-500')
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' })
          }
        })

        return false
      }

      this.$nextTick(() => {
        const firstErrorField = document.querySelector('.is-invalid')
        if (firstErrorField) {
          firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      })

      return Object.keys(this.errors).length === 0
    },
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
      const oldStocks = [...this.stocks]
      const oldStockData = JSON.parse(JSON.stringify(this.form.stock_data))

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

      let selected = []

      if (this.form.variant_input_mode === 'from_category') {
        selected = this.selectedAttributes
          .filter(attr => {
            const values = this.selectedAttributeValues[attr.id]
            return attr && attr.id && Array.isArray(values) && values.length > 0
          })
          .map(attr => ({
            attr,
            values: this.selectedAttributeValues[attr.id]
          }))
      } else {
        selected = this.form.custom_attributes
          .filter(attr => attr.title.trim() && attr.values.length > 0)
          .map(attr => ({
            attr: {
              id: attr.id,
              title: attr.title,
              attributes: attr.values
            },
            values: attr.values.map(v => v.id)
          }))
      }

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

        this.stocks.splice(0, this.stocks.length, ...oldStocks)

        Object.keys(this.form.stock_data).forEach(k => {
          this.$delete(this.form.stock_data, k)
        })
        Object.keys(oldStockData).forEach(k => {
          this.$set(this.form.stock_data, k, oldStockData[k])
        })

        return
      }

      // Không vượt → áp dụng thay đổi
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
      const original = this.trashVariants.find(v =>
        v.stock_id === variant.stock_id &&
        this.isSameAttributes(v.attributes, variant.attributes)
      )
      const restored = original
        ? JSON.parse(JSON.stringify(original))
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

      if (!this.form.category_id && this.form.has_variant) {
        this.form.has_variant = false;
        this.form.type = 'single';
        Swal.fire({
          icon: 'warning',
          title: this.$t('product.category_required_title') || 'Thiếu danh mục',
          text: this.$t('product.category_required_msg') || 'Vui lòng chọn danh mục trước khi bật biến thể.',
          confirmButtonText: 'OK'
        });
        return;
      }

      if (this.mode === 'create' && this.form.variant_input_mode === 'from_category') {
        this.resetVariantData();
      }

      this.checkAndLoadVariants();
    },
    resetVariantData() {
      this.form.custom_attributes = [];
      this.selectedAttributes = [];
      Object.keys(this.selectedAttributeValues).forEach(attrId => {
        this.selectedAttributeValues[attrId] = []
      });
      this.form.variants = [];
      this.trashVariants = [];
      this.previewAttributes = [];
    },
    async onChangeVariantInputMode(e) {
      const newMode = e.target.value;

      if (newMode === 'from_category' && !this.form.category_id) {
        await Swal.fire({
          icon: 'warning',
          title: 'Thiếu danh mục',
          text: 'Vui lòng chọn danh mục trước khi chọn "Lấy từ danh mục"',
          confirmButtonText: 'OK'
        });
        this.form.variant_input_mode = 'create';
        return;
      }

      // Nếu là form update → cảnh báo
      if (this.mode === 'update' && newMode !== this.variant_input_mode_original) {
        const confirm = await Swal.fire({
          icon: 'warning',
          title: 'Thay đổi nguồn biến thể',
          text: 'Việc chuyển nguồn biến thể sẽ làm mất dữ liệu hiện tại. Bạn có chắc chắn không?',
          showCancelButton: true,
          confirmButtonText: 'Đồng ý',
          cancelButtonText: 'Huỷ'
        });

        if (!confirm.isConfirmed) {
          // Rollback lại select
          this.$nextTick(() => {
            this.form.variant_input_mode = this.variant_input_mode_original;
          });
          return;
        }
      }

      this.form.variant_input_mode = newMode;

      // Nếu đang tạo hoặc đã xác nhận reset
      if (this.mode === 'create' || this.mode === 'update') {
        this.resetVariantData();
      }

      this.generateVariantGrid();
    },
    async onVariantCheckboxChange(e) {
      const willUncheck = !e.target.checked;

      if (this.form.variant_input_mode === 'from_category' && !this.form.category_id) {
        this.form.has_variant = false;
        await Swal.fire({
          icon: 'warning',
          title: this.$t('product.category_required_title') || 'Thiếu danh mục',
          text: this.$t('product.category_required_msg') || 'Vui lòng chọn danh mục trước khi bật biến thể.',
          confirmButtonText: 'OK'
        });
        return;
      }

      if (this.mode === 'update' && this.hasVariantInitial && willUncheck && this.form.variant_input_mode ===   'from_category') {
        const result = await Swal.fire({
          title: this.$t('product.confirm_remove_variant_title'),
          text: this.$t('product.confirm_remove_variant_text'),
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: this.$t('product.confirm_remove_variant_button_text'),
          cancelButtonText: this.$t('product.cancel_button_text'),
        });

        if (!result.isConfirmed) {
          this.form.has_variant = true;
          return;
        }
      }

      this.onToggleVariantLogic();
    },
    onToggleVariantLogic() {
      this.form.variant_input_mode = 'create'
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
      this.isLoadingAttributes = true
      try {
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
      } catch (error) {
          console.log(error);
      } finally {
          this.isLoadingAttributes = false
      }
    },
    generateVariantGrid: _.debounce(function () {
      let selected = []

      if (this.form.variant_input_mode === 'from_category') {
        selected = this.selectedAttributes
          .filter(attr => {
            const values = this.selectedAttributeValues[attr.id]
            return attr && attr.id && Array.isArray(values) && values.length > 0
          })
          .map(attr => ({
            attr,
            values: this.selectedAttributeValues[attr.id]
          }))
      } else {
        selected = this.form.custom_attributes
          .filter(attr => attr.title.trim() && attr.values.length > 0)
          .map(attr => ({
            attr: {
              id: attr.id,
              title: attr.title,
              attributes: attr.values
            },
            values: attr.values.map(v => v.id)
          }))
      }

      if (selected.length === 0) {
        this.form.variants = []
        this.previewAttributes = []
        return
      }

      this.previewAttributes = selected.map(s => s.attr.title)

       const combinations = this.generateCombinations(
        selected.map(item => ({
          attribute: item.attr,
          values: item.values
        }))
      )

      const newVariants = []

      combinations.forEach(combo => {
        this.stocks.forEach(stock => {
          const old = this.form.variants.find(v =>
            v.stock_id === stock.stock_id && this.isSameAttributes(v.attributes, combo)
          )

          const isTrashed = this.trashVariants.some(tv =>
            tv.stock_id === stock.stock_id && this.isSameAttributes(tv.attributes, combo)
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
    }, 200),
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
        this.form.custom_attributes = product.custom_attributes
        this.variant_input_mode_original = product.variant_input_mode
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

          this.previewAttributes = (product.variant_input_mode === 'from_Category')
          ? this.selectedAttributes.map(a => a.title)
          : this.form.custom_attributes.map(a => a.title)
        }

    },
    handleCoverImage(e) {
      const file = e.target.files[0]
      this.form.cover_image = file
    },
    removeCoverImage() {
      this.form.cover_image = null
      this.form.remove_cover_image = true
    },
    handleGalleryImages(e) {
      const maxGalleryImages = 9;
      const newFiles = Array.from(e.target.files).map(file => ({
        file,
        isOld: false
      }));

      const totalImages = this.form.gallery_images.length + newFiles.length;

      if (totalImages > maxGalleryImages) {
        Swal.fire({
          icon: 'error',
          title: this.$t('product.limit_gallery_title') || 'Vượt quá giới hạn',
          text: this.$t('product.limit_gallery_text') || `Bạn chỉ được chọn tối đa ${maxGalleryImages} ảnh.`,
          confirmButtonText: 'OK'
        });
        return;
      }

      this.form.gallery_images.push(...newFiles);
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
      if (!this.validateForm()) return
      this.loading = true
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
        if (this.form.remove_cover_image) {
          formData.append('remove_cover_image', 1)
        }
        if (this.removed_variant_image_ids.length) {
          this.removed_variant_image_ids.forEach(id => {
            formData.append('removed_variant_image_ids[]', id)
          })
        }
        formData.append('custom_attributes', JSON.stringify(this.form.custom_attributes))
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
        title: res?.data?.message,
        showConfirmButton: false,
        timer: 1500
      })
      this.resetCacheableListByKey()
      this.$router.push('/warehouse/product')
      } catch (err) {
        const res = err?.response?.data || {}
        Swal.fire({
          icon: 'error',
          title: res.message,
          text: res.message
        })
      } finally {
        this.loading = false
      }
    },
  }
}
</script>