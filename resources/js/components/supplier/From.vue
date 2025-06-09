<template>
  <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">
    <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
  </div>

  <div class="p-6 bg-white rounded-xl shadow-md w-full">
    <h2 class="text-2xl font-bold mb-6">{{ mode === 'create' ? 'Thêm nhà cung cấp' : 'Chỉnh sửa nhà cung cấp' }}</h2>

    <form @submit.prevent="submitForm" class="space-y-8" enctype="multipart/form-data">
      <!-- 🖼 Avatar -->
      <div>
        <label class="block mb-2 text-sm font-semibold text-gray-700">Logo / Ảnh đại diện</label>
        <div class="flex items-start gap-6">
          <div>
            <input
              ref="avatarInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleAvatarChange"
            />
            <div
              @click="$refs.avatarInput.click()"
              class="w-[120px] h-[120px] border-2 border-dashed border-gray-400 rounded-md flex items-center justify-center cursor-pointer hover:border-blue-500 relative group"
            >
              <img
                v-if="avatarPreview"
                :src="avatarPreview"
                class="absolute inset-0 w-full h-full object-cover rounded-md"
              />
              <span v-else class="text-gray-400 text-4xl">＋</span>
              <button
                v-if="avatarPreview"
                type="button"
                @click.stop="removeAvatar"
                class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700"
              >✕</button>
            </div>
          </div>
        </div>
      </div>

      <!-- 👤 Thông tin nhà cung cấp -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Thông tin nhà cung cấp</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tên nhà cung cấp <span class="text-red-500">*</span></label>
            <input ref="name" v-model="form.name" type="text" @input="clearError('name')" :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.name ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã nhà cung cấp <span class="text-red-500">*</span></label>
            <input ref="code" v-model="form.code" type="text" @input="clearError('code')" :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.code ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.code" class="text-sm text-red-600 mt-1">{{ errors.code[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">SĐT <span class="text-red-500">*</span></label>
            <input ref="phone" v-model="form.phone" type="text" @input="clearError('phone')" :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.phone ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.phone" class="text-sm text-red-600 mt-1">{{ errors.phone[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Email</label>
            <input v-model="form.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
            <input ref="address" v-model="form.address" type="text" @input="clearError('address')" :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.address ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.address" class="text-sm text-red-600 mt-1">{{ errors.address[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Quốc gia</label>
            <select v-model="form.country_code" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="VN">Việt Nam</option>
              <option value="JP">Nhật Bản</option>
              <option value="US">Hoa Kỳ</option>
              <option value="KR">Hàn Quốc</option>
              <option value="CN">Trung Quốc</option>
            </select>
          </div>

          <template v-if="form.country_code === 'VN'">
            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
              <select @change="onProvinceChange" ref="company_province_id" v-model="form.company_province_id" :class="['w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400', errors.company_province_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">Chọn tỉnh</option>
                <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.full_name }}</option>
              </select>
              <p v-if="errors.company_province_id" class="text-sm text-red-600 mt-1">{{ errors.company_province_id[0] }}</p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Quận / Huyện <span class="text-red-500">*</span></label>
              <select @change="onDistrictChange" ref="company_district_id" v-model="form.company_district_id" :disabled="!form.company_province_id || isDistrictLoading" :class="['w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400', errors.company_district_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">Chọn huyện</option>
                <option v-for="d in districts" :key="d.code" :value="d.code">{{ d.full_name }}</option>
              </select>
              <p v-if="errors.company_district_id" class="text-sm text-red-600 mt-1">{{ errors.company_district_id[0] }}</p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Phường / Xã <span class="text-red-500">*</span></label>
              <select ref="company_ward_id" v-model="form.company_ward_id" :disabled="!form.company_district_id || isWardLoading" :class="['w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400', errors.company_ward_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">Chọn xã</option>
                <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.full_name }}</option>
              </select>
              <p v-if="errors.company_ward_id" class="text-sm text-red-600 mt-1">{{ errors.company_ward_id[0] }}</p>
            </div>
          </template>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Trạng thái</label>
            <select v-model="form.status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="active">Hoạt động</option>
              <option value="inactive">Ngưng hoạt động</option>
              <option value="blacklist">Black list</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 💼 Thông tin doanh nghiệp -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Thông tin doanh nghiệp</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã số thuế</label>
            <input v-model="form.tax_code" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Công nợ</label>
            <input v-model="form.debt_amount" type="number" step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Hạn mức tín dụng</label>
            <input v-model="form.credit_limit" type="number" step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>
      </div>

      <!-- 📄 Ghi chú -->
      <div>
        <label class="block mb-1 text-sm font-semibold text-gray-700">Ghi chú</label>
        <textarea v-model="form.note" rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
      </div>

      <!-- 🔘 Submit -->
      <div class="pt-4 mt-6">
        <button type="submit"
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer">
          📂 Lưu nhà cung cấp
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    mode: { type: String, default: 'create' },
    supplierId: { type: [String, Number], default: null }
  },
  data() {
    return {
      form: {
        country_code: 'VN', name: '', code: '', phone: '', email: '', address: '',
        company_province_id: '', company_district_id: '', company_ward_id: '', status: 'active',
        tax_code: '', debt_amount: 0, credit_limit: 0, note: ''
      },
      avatarFile: null,
      avatarPreview: null,
      originalForm: null,
      errors: {},
      provinces: [],
      districts: [],
      wards: [],
      loading: true,
      isDistrictLoading: false,
      isWardLoading: false,
      skipChangeHandler: true,
      removeAvatarFlag: false
    }
  },
  async mounted() {
    try {
      if (this.mode === 'update' && this.supplierId) {
        this.skipChangeHandler = true

        const [provinceData, supplierRes] = await Promise.all([
          this.fetchProvinces(),
          window.axios.get(`/api/supplier/detail/${this.supplierId}`)
        ])

        const { data } = supplierRes.data
        Object.assign(this.form, data)
        this.avatarPreview = data.avatar_url || null

        await this.loadAllLocationOnUpdate()

        this.originalForm = JSON.stringify(this.form)
        this.skipChangeHandler = false
      } else {
        await this.fetchProvinces()
        this.originalForm = JSON.stringify(this.form)
        this.skipChangeHandler = false
      }
    } catch (err) {
      this.$router.push('/purchase/supplier')
    } finally {
      this.loading = false
    }

    window.addEventListener('beforeunload', this.handleBeforeUnload)
  },
  beforeDestroy() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload)
  },
  methods: {
    async loadAllLocationOnUpdate() {
      const tasks = []

      if (this.form.company_province_id) {
        this.isDistrictLoading = true
        tasks.push(
          window.axios.get('/api/common/districts', {
            params: { province_code: this.form.company_province_id }
          }).then(res => {
            this.districts = res.data.data
          })
        )
      }

      if (this.form.company_district_id) {
        this.isWardLoading = true
        tasks.push(
          window.axios.get('/api/common/wards', {
            params: { district_code: this.form.company_district_id }
          }).then(res => {
            this.wards = res.data.data
          })
        )
      }

      await Promise.all(tasks)

      this.isDistrictLoading = false
      this.isWardLoading = false
    },
    async onProvinceChange() {
      if (this.skipChangeHandler) return
      this.form.company_district_id = ''
      this.form.company_ward_id = ''
      this.districts = []
      this.wards = []
      if (this.form.company_province_id) {
        this.isDistrictLoading = true
        const res = await window.axios.get('/api/common/districts', {
          params: { province_code: this.form.company_province_id }
        })
        this.districts = res.data.data
        this.isDistrictLoading = false
      }
    },
    async onDistrictChange() {
      if (this.skipChangeHandler) return
      this.form.company_ward_id = ''
      this.wards = []

      if (this.form.company_district_id) {
        this.isWardLoading = true
        const res = await window.axios.get('/api/common/wards', {
          params: { district_code: this.form.company_district_id }
        })
        this.wards = res.data.data
        this.isWardLoading = false
      }
    },
    clearError(field) {
      if (this.errors[field]) this.errors[field] = null
    },
    handleAvatarChange(e) {
      const file = e.target.files[0]
      if (file) {
        this.avatarFile = file
        this.avatarPreview = URL.createObjectURL(file)
      }
    },
    removeAvatar() {
      this.avatarFile = null
      this.avatarPreview = null
      this.$refs.avatarInput.value = null
      this.removeAvatarFlag = true
    },
    handleBeforeUnload(e) {
      if (this.isFormDirty()) {
        e.preventDefault()
        e.returnValue = ''
      }
    },
    isFormDirty() {
      return JSON.stringify(this.form) !== this.originalForm || this.avatarFile !== null
    },
    async fetchProvinces() {
      const { data } = await window.axios.get('/api/common/provinces')
      this.provinces = data.data
    },
    checkValidate(){
      this.errors = {}

      if (!this.form.name) this.errors.name = ['Tên nhà cung cấp là bắt buộc']
      if (!this.form.code) this.errors.code = ['Mã nhà cung cấp là bắt buộc']
      if (!this.form.phone) this.errors.phone = ['SĐT là bắt buộc']
      if (!this.form.address) this.errors.address = ['Địa chỉ là bắt buộc']
      if (this.form.country_code === 'VN') {
        if (!this.form.company_province_id) this.errors.company_province_id = ['Tỉnh là bắt buộc']
        if (!this.form.company_district_id) this.errors.company_district_id = ['Huyện là bắt buộc']
        if (!this.form.company_ward_id) this.errors.company_ward_id = ['Xã là bắt buộc']
      }
      if (Object.keys(this.errors).length > 0) {
        const firstError = Object.keys(this.errors)[0]
        this.$nextTick(() => {
          const el = this.$refs[firstError]
          if (el?.scrollIntoView) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' })
          }
        })
        return false
      } else {
        return true
      }
    },
    async submitForm() {
      if(!this.checkValidate()) return
      const formData = new FormData()
      for (const key in this.form) {
          if (key !== 'avatar') {
            formData.append(key, this.form[key] ?? '')
          }
      }
      if (this.avatarFile instanceof File) {
        formData.append('avatar', this.avatarFile)
      }
      if (this.removeAvatarFlag) {
        formData.append('remove_avatar', true)
      }
      try {
        if (this.mode === 'update') {
          await window.axios.post(`/api/supplier/update/${this.supplierId}`, formData)
        } else {
          await window.axios.post('/api/supplier/create', formData)
        }
        this.$router.push('/purchase/supplier')
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors
          const firstError = Object.keys(this.errors)[0]
          this.$nextTick(() => {
            const el = this.$refs[firstError]
            if (el?.scrollIntoView) {
              el.scrollIntoView({ behavior: 'smooth', block: 'center' })
            }
          })
        } else {
          alert('Có lỗi xảy ra!')
        }
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    if (this.isFormDirty()) {
      const confirmLeave = confirm('Bạn có chắc muốn rời trang khi dữ liệu chưa lưu?')
      next(confirmLeave)
    } else {
      next()
    }
  }
}
</script>
<style>

</style>