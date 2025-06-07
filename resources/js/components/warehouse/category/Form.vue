<template>
  <div>
    <!-- Loader -->
    <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">
      <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
      </svg>
    </div>

    <div class="space-y-6 p-4 bg-white rounded-xl shadow-md">
      <h1 class="text-2xl font-bold">
        {{ mode === 'create' ? $t('category.add_title') : $t('category.edit_title') }}
      </h1>

      <!-- Tên danh mục -->
      <div class="mb-4">
        <label class="block font-medium mb-1">{{ $t('category.title') }} <span class="text-red-500">*</span></label>
        <input
          v-model="form.title"
          :placeholder="$t('category.title')"
          class="w-1/2 border px-4 py-2 rounded shadow-sm focus:outline-none focus:ring-2"
          :class="{ 'border-red-500': !form.title.trim() }"
        />
        <p v-if="!form.title.trim()" class="text-red-500 text-xs mt-1">{{ $t('category.required') }}</p>
      </div>

      <!-- Danh sách biến thể -->
      <div v-for="(variant, vIndex) in form.variants" :key="vIndex" class="mb-6">
        <div class="border p-4 rounded bg-gray-50 w-1/2">
          <div class="flex justify-between items-center mb-2">
            <h3 class="font-semibold">
              🔸 {{ $t('category.variant_title', { index: vIndex + 1 }) }} <span class="text-red-500">*</span>
            </h3>
            <button @click="removeVariant(vIndex)" class="text-red-500 hover:underline text-sm">
              {{ $t('category.delete_variant') }}
            </button>
          </div>

          <input
            v-model="variant.title"
            :placeholder="$t('category.variant_name_placeholder')"
            class="w-full border px-4 py-2 rounded shadow-sm mb-3"
            :class="{ 'border-red-500': !variant.title.trim() }"
          />
          <p v-if="!variant.title.trim()" class="text-red-500 text-xs mb-2">{{ $t('category.required') }}</p>

          <!-- Danh sách thuộc tính -->
          <div v-for="(attr, aIndex) in variant.attributes" :key="aIndex" class="flex items-center gap-2 mb-2">
            <div class="w-full relative">
              <input
                v-model="attr.title"
                :placeholder="$t('category.attribute_name_placeholder')"
                class="w-full border px-3 py-2 rounded shadow-sm"
                :class="{
                  'border-red-500': !attr.title.trim() || isDuplicateAttribute(vIndex, attr.title, aIndex)
                }"
              />
              <p v-if="!attr.title.trim()" class="text-red-500 text-xs mt-1">{{ $t('category.required') }}</p>
              <p
                v-else-if="isDuplicateAttribute(vIndex, attr.title, aIndex)"
                class="text-red-500 text-xs mt-1"
              >
                {{ $t('category.duplicate_attribute') }}
              </p>
            </div>
            <button @click="removeAttribute(vIndex, aIndex)" class="text-red-400 hover:text-red-600 text-sm">❌</button>
          </div>

          <button
            @click="addAttribute(vIndex)"
            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm"
            :disabled="variant.attributes.length >= 10"
          >
            {{ $t('category.add_attribute') }} ({{ variant.attributes.length }}/10)
          </button>
        </div>
      </div>

      <!-- Thêm biến thể -->
      <div class="mb-4">
        <button @click="addVariant" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
          {{ $t('category.add_variant') }}
        </button>
      </div>

      <!-- Lưu -->
      <button @click="submit" class="bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-800 font-semibold">
        {{ $t('category.save') }}
      </button>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2'

export default {
  props: {
    mode: {
      type: String,
      default: 'create',
    },
    id: {
      type: [String, Number],
      default: null,
    },
  },
  data() {
    return {
      form: {
        title: '',
        variants: [],
      },
      loading : true,
      formBackup: null
    };
  },
 async created() {
    if (this.mode === 'edit' && this.id) {
        await this.getDetail()
    }
    this.loading = false;
  },
  methods: {
    async getDetail() {
      try {
        const res = await window.axios.get(`/api/warehouse/category/detail/${this.id}`)
        this.form = res.data.data
        this.formBackup = JSON.parse(JSON.stringify(this.form))
      } catch (e) {
        console.error('Lỗi khi load dữ liệu:', e)
      }
    },
    async handleSubmit() {
      this.loading = true;
      try {
        const url = this.mode === 'edit'
          ? `/api/warehouse/category/update/${this.id}`
          : `/api/warehouse/category/create`
        await window.axios.post(url, this.form)
        this.$router.push('/warehouse/category')
      } catch (e) {
        const res = e?.response?.data
        if (this.formBackup) {
          this.form = JSON.parse(JSON.stringify(this.formBackup))
          this.$nextTick(() => {
            document.querySelector('input')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
          })
        }
        await Swal.fire({
          icon: 'error',
          title: res?.message || 'Đã xảy ra lỗi!',
          text: typeof res?.errors === 'string' ? res.errors : 'Vui lòng kiểm tra lại dữ liệu.',
        })
      } finally {
        this.loading = false
      }
    },
    submit() {
      this.handleSubmit()
    },
    addVariant() {
      this.form.variants.push({ title: '', attributes: [] })
    },
    removeVariant(index) {
      this.form.variants.splice(index, 1)
    },
    addAttribute(vIndex) {
      if (this.form.variants[vIndex].attributes.length < 10) {
        this.form.variants[vIndex].attributes.push({ title: '' })
      }
    },
    removeAttribute(vIndex, aIndex) {
      this.form.variants[vIndex].attributes.splice(aIndex, 1)
    },
    isDuplicateAttribute(vIndex, value, currentIndex) {
      const list = this.form.variants[vIndex].attributes.map(a => a.title?.trim().toLowerCase())
      return list.filter((val, idx) => val === value?.trim().toLowerCase() && idx !== currentIndex).length > 0
    }
  },
};
</script>
