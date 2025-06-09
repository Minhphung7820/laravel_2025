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
            <input
              v-model="form.name"
              type="text"
              @input="clearError('name')"
              :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.name ? 'border-red-500' : 'border-gray-300']"
            />
            <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã nhà cung cấp</label>
            <input v-model="form.code" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">SĐT</label>
            <input v-model="form.phone" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Email</label>
            <input v-model="form.email" type="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Địa chỉ chi tiết</label>
            <input v-model="form.address" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Quốc gia</label>
            <select
              v-model="form.country_code"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
              <option value="VN">Việt Nam</option>
              <option value="JP">Nhật Bản</option>
              <option value="US">Hoa Kỳ</option>
              <option value="KR">Hàn Quốc</option>
              <option value="CN">Trung Quốc</option>
              <!-- Thêm quốc gia khác tùy ý -->
            </select>
          </div>

          <template v-if="form.country_code === 'VN'">
            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Tỉnh / Thành phố</label>
              <select v-model="form.province_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Chọn tỉnh</option>
                <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
              </select>
            </div>

            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Quận / Huyện</label>
              <select v-model="form.district_id" :disabled="!form.province_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Chọn huyện</option>
                <option v-for="d in districts" :key="d.code" :value="d.code">{{ d.name }}</option>
              </select>
            </div>

            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Phường / Xã</label>
              <select v-model="form.ward_id" :disabled="!form.district_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Chọn xã</option>
                <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.name }}</option>
              </select>
            </div>
          </template>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Trạng thái</label>
            <select v-model="form.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
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
    mode: {
      type: String,
      default: 'create'
    },
    supplierId: {
      type: [String, Number],
      default: null
    }
  },
  data() {
    return {
      form: {
        country_code: 'VN',
        name: '',
        phone: '',
        email: '',
        birthday: '',
        gender: '',
        address: '',
        type: 'individual',
        source: '',
        status: 'active',
        assigned_user_id: null,
        facebook_url: '',
        zalo_phone: '',
        tax_code: '',
        debt_amount: 0,
        credit_limit: 0,
        note: ''
      },
      avatarFile: null,
      avatarPreview: null,
      originalForm: null,
      errors: {}
    }
  },
  async mounted() {
    if (this.mode === 'update' && this.supplierId) {
      try {
        const supplier = await window.axios.get(`/api/supplier/detail/${this.supplierId}`)
        const { data } = supplier.data
        this.form = { ...this.form, ...data }
        this.form.avatar = null;
        this.form.birthday = data.birthday ? data.birthday.split('T')[0] : ''
        this.avatarPreview = data.avatar_url || null
        this.originalForm = JSON.stringify(this.form)
      } catch (err) {
        alert('Không tìm thấy nhà cung cấp')
        this.$router.push('/purchase/supplier')
      }
    } else {
      this.originalForm = JSON.stringify(this.form)
    }

    window.addEventListener('beforeunload', this.handleBeforeUnload)
  },
  beforeDestroy() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload)
  },
  methods: {
    clearError(field) {
      if (this.errors[field]) {
        this.$set(this.errors, field, null)
      }
    },
    handleAvatarChange(e) {
      const file = e.target.files[0]
      if (!file) return
      this.avatarFile = file
      this.avatarPreview = URL.createObjectURL(file)
    },
    removeAvatar() {
      this.avatarFile = null
      this.avatarPreview = null
    },
    async isFormDirty() {
      return JSON.stringify(this.form) !== this.originalForm ||
        this.avatarFile !== null
    },
    handleBeforeUnload(e) {
      if (this.isFormDirty()) {
        e.preventDefault()
        e.returnValue = ''
      }
    },
    async submitForm() {
      try {
        const formData = new FormData()
        for (const key in this.form) {
          formData.append(key, this.form[key] ?? '')
        }
        if (this.avatarFile) {
          formData.append('avatar', this.avatarFile)
        }

        if (this.mode === 'update') {
          await window.axios.post(`/api/supplier/update/${this.supplierId}`, formData)
        } else {
          await window.axios.post('/api/supplier/create', formData)
        }

        this.originalForm = JSON.stringify(this.form)
        this.avatarFile = null

        this.$router.push('/purchase/supplier')
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.errors = err.response.data.errors || {}
        } else {
          console.error(err)
          alert('Gửi thất bại!')
        }
      }
    }
  },
  async beforeRouteLeave(to, from, next) {
    const checkIsInputing = await this.isFormDirty();
    if (checkIsInputing) {
      const answer = confirm('Bạn có chắc muốn rời trang khi dữ liệu chưa lưu?')
      next(answer)
    } else {
      next()
    }
  }
}
</script>

<style>

</style>