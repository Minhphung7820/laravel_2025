<template>
  <div class="p-6 bg-white rounded-xl shadow-md w-full">
    <h2 class="text-2xl font-bold mb-6">Thêm khách hàng</h2>

    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Thông tin cơ bản -->
      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Tên khách hàng <span class="text-red-500">*</span></label>
        <input
          v-model="form.name"
          type="text"
          @input="clearError('name')"
          :class="[
            'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
            errors.name ? 'border-red-500' : 'border-gray-300'
          ]"
        />
        <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">
          SĐT <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.phone"
          @input="clearError('phone')"
          type="text"
          :class="[
            'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
            errors.phone ? 'border-red-500' : 'border-gray-300'
          ]"
        />
        <p v-if="errors.phone" class="text-sm text-red-600 mt-1">{{ errors.phone[0] }}</p>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Email</label>
        <input v-model="form.email" type="email"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Ngày sinh</label>
        <input v-model="form.birthday" type="date"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Giới tính</label>
        <select v-model="form.gender"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
          <option value="">-- Không chọn --</option>
          <option value="male">Nam</option>
          <option value="female">Nữ</option>
          <option value="other">Khác</option>
          <option value="unknown">Không rõ</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Địa chỉ</label>
        <input v-model="form.address" type="text"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <!-- Phân loại -->
      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Loại khách hàng</label>
        <select v-model="form.type"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
          <option value="individual">Cá nhân</option>
          <option value="company">Công ty</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Nguồn khách</label>
        <input v-model="form.source" type="text" placeholder="Facebook, Zalo..."
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Trạng thái</label>
        <select v-model="form.status"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Ngưng hoạt động</option>
          <option value="blacklist">Black list</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Nhân viên phụ trách</label>
        <input v-model="form.assigned_user_id" type="number"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <!-- Social -->
      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Facebook URL</label>
        <input v-model="form.facebook_url" type="text"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">SĐT Zalo</label>
        <input v-model="form.zalo_phone" type="text"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <!-- Công nợ -->
      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Mã số thuế</label>
        <input v-model="form.tax_code" type="text"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Công nợ</label>
        <input v-model="form.debt_amount" type="number" step="0.01"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Hạn mức tín dụng</label>
        <input v-model="form.credit_limit" type="number" step="0.01"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <!-- Ghi chú -->
      <div class="md:col-span-2">
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Ghi chú</label>
        <textarea v-model="form.note" rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block mb-1 text-sm text-gray-700 font-semibold">Ảnh đại diện</label>

        <!-- Preview -->
        <div v-if="avatarPreview" class="mb-2 relative w-[120px]">
          <img :src="avatarPreview" class="rounded shadow w-[120px] h-[120px] object-cover border" />
          <button type="button" @click="removeAvatar"
            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700">
            ✕
          </button>
        </div>

        <!-- Chọn ảnh -->
        <input type="file" accept="image/*" @change="handleAvatarChange"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <!-- Submit -->
      <div class="md:col-span-2 flex justify-end pt-4 border-t mt-6">
        <button type="submit"
          class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow font-semibold cursor-pointer">
          💾 Lưu khách hàng
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
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
  mounted() {
    this.originalForm = JSON.stringify(this.form)
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
    validateForm() {
      if (!this.form.name.trim()) {
        alert('Tên khách hàng là bắt buộc')
        return false
      }
      if (!this.form.phone.trim()) {
        alert('Số điện thoại là bắt buộc')
        return false
      }

      return true
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
      // if (!this.validateForm()) return
      try {
        const formData = new FormData()
        for (const key in this.form) {
          formData.append(key, this.form[key])
        }
        if (this.avatarFile) {
          formData.append('avatar', this.avatarFile)
        }

        await window.axios.post('/api/customer/create', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        this.originalForm = JSON.stringify(this.form)
        this.avatarFile = null

        this.$router.push('/sale/customer')
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