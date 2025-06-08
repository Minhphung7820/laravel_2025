<template>
  <div class="p-6 bg-white rounded-xl shadow-md w-full">
    <h2 class="text-2xl font-bold mb-6">{{ mode === 'create' ? 'Thêm khách hàng' : 'Chỉnh sửa khách hàng' }}</h2>

    <form @submit.prevent="submitForm" class="space-y-8" enctype="multipart/form-data">

      <!-- 🖼 Avatar -->
      <div>
        <label class="block mb-2 text-sm font-semibold text-gray-700">Ảnh đại diện</label>
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

      <!-- 👤 Thông tin cơ bản -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Thông tin cơ bản</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tên khách hàng <span class="text-red-500">*</span></label>
            <input ref="name" v-model="form.name" type="text" @input="clearError('name')"
              :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.name ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">SĐT <span class="text-red-500">*</span></label>
            <input ref="phone" v-model="form.phone" type="text" @input="clearError('phone')"
              :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.phone ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.phone" class="text-sm text-red-600 mt-1">{{ errors.phone[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã KH <span class="text-red-500">*</span></label>
            <input v-model="form.code" ref="code" type="text" @input="clearError('code')"
              :class="['w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400', errors.code ? 'border-red-500' : 'border-gray-300']" />
            <p v-if="errors.code" class="text-sm text-red-600 mt-1">{{ errors.code[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Email</label>
            <input v-model="form.email" type="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Ngày sinh</label>
            <input v-model="form.birthday" type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Giới tính</label>
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
            <label class="block mb-1 text-sm font-semibold text-gray-700">Địa chỉ</label>
            <input v-model="form.address" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tỉnh/Thành</label>
            <select v-model="form.province_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Chọn tỉnh --</option>
              <option v-for="item in provinces" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Quận/Huyện</label>
            <select v-model="form.district_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Chọn huyện --</option>
              <option v-for="item in districts" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Phường/Xã</label>
            <select v-model="form.ward_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Chọn phường --</option>
              <option v-for="item in wards" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 🌐 Liên hệ -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Liên hệ & Mạng xã hội</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Facebook URL</label>
            <input v-model="form.facebook_url" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">SĐT Zalo</label>
            <input v-model="form.zalo_phone" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Nguồn khách</label>
            <input v-model="form.source" type="text" placeholder="Facebook, Zalo..."
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Kênh ưu tiên</label>
            <select v-model="form.preferred_contact"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Không chọn --</option>
              <option value="phone">Gọi điện</option>
              <option value="email">Email</option>
              <option value="zalo">Zalo</option>
              <option value="sms">SMS</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Lần liên hệ cuối</label>
            <input v-model="form.last_contacted_at" type="datetime-local"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Đánh giá</label>
            <select v-model.number="form.rating"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Chọn --</option>
              <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">UTM Source</label>
            <input v-model="form.utm_source" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">UTM Medium</label>
            <input v-model="form.utm_medium" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">UTM Campaign</label>
            <input v-model="form.utm_campaign" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>
      </div>

      <!-- 🧩 Phân loại -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Phân loại khách hàng</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Loại khách hàng</label>
            <select v-model="form.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="individual">Cá nhân</option>
              <option value="company">Công ty</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Trạng thái</label>
            <select v-model="form.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="active">Đang hoạt động</option>
              <option value="inactive">Ngưng hoạt động</option>
              <option value="blacklist">Black list</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Nhân viên phụ trách</label>
            <input v-model="form.assigned_user_id" type="number"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Nhóm khách hàng</label>
            <select v-model="form.customer_group_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">-- Chọn nhóm --</option>
              <option v-for="item in customerGroups" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Giai đoạn khách</label>
            <select v-model="form.customer_stage"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="new">Mới</option>
              <option value="prospect">Tiềm năng</option>
              <option value="converted">Đã chuyển đổi</option>
              <option value="inactive">Không hoạt động</option>
              <option value="lost">Mất</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tần suất liên hệ (ngày)</label>
            <input v-model.number="form.contact_frequency_days" type="number" min="1"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>
      </div>
      <template v-if="form.type === 'company'">
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Thông tin công ty</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Tên công ty -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tên công ty <span class="text-red-500">*</span></label>
            <input
                ref="company_name"
                v-model="form.company_name"
                type="text"
                :class="[
                  'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                  errors.company_name ? 'border-red-500' : 'border-gray-300'
                ]"
            />
            <p v-if="errors.company_name" class="text-sm text-red-600 mt-1">{{ errors.company_name[0] }}</p>
          </div>

          <!-- Chức vụ -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Chức vụ</label>
            <input v-model="form.position" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Website -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Website</label>
            <input v-model="form.website_url" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Số nhân viên -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Số nhân viên</label>
            <input v-model.number="form.number_of_employees" type="number" min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Doanh thu dự kiến -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Doanh thu dự kiến</label>
            <input v-model.number="form.revenue_estimate" type="number" min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Mã số thuế -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã số thuế</label>
            <input v-model="form.company_tax_code" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Ngày thành lập -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">Ngày thành lập</label>
            <input v-model="form.founded_at" type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <!-- Người đại diện -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">
              Người đại diện <span class="text-red-500">*</span>
            </label>
            <input
              ref="representative_name"
              v-model="form.representative_name"
              type="text"
              :class="[
                'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                errors.representative_name ? 'border-red-500' : 'border-gray-300'
              ]"
            />
            <p v-if="errors.representative_name" class="text-sm text-red-600 mt-1">
              {{ errors.representative_name[0] }}
            </p>
          </div>


          <!-- Địa chỉ công ty -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">
            Địa chỉ công ty <span class="text-red-500">*</span>
            </label>
            <input
              ref="company_address"
              v-model="form.company_address"
              type="text"
              :class="[
                'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                errors.company_address ? 'border-red-500' : 'border-gray-300'
              ]"
            />
            <p v-if="errors.company_address" class="text-sm text-red-600 mt-1">
              {{ errors.company_address[0] }}
            </p>
          </div>

          <!-- Tỉnh/Thành -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">
              Tỉnh/Thành (công ty) <span class="text-red-500">*</span>
            </label>
            <select
              ref="company_province_id"
              v-model="form.company_province_id"
              :class="[
                'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                errors.company_province_id ? 'border-red-500' : 'border-gray-300'
              ]"
            >
              <option value="">-- Chọn tỉnh --</option>
              <option v-for="item in provinces" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <p v-if="errors.company_province_id" class="text-sm text-red-600 mt-1">
              {{ errors.company_province_id[0] }}
            </p>
          </div>

          <!-- Quận/Huyện -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">
              Quận/Huyện (công ty) <span class="text-red-500">*</span>
            </label>
            <select
              ref="company_district_id"
              v-model="form.company_district_id"
              :class="[
                'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                errors.company_district_id ? 'border-red-500' : 'border-gray-300'
              ]"
            >
              <option value="">-- Chọn huyện --</option>
              <option v-for="item in districts" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <p v-if="errors.company_district_id" class="text-sm text-red-600 mt-1">
              {{ errors.company_district_id[0] }}
            </p>
          </div>

          <!-- Phường/Xã -->
          <div>
            <label class="block mb-1 text-sm font-semibold text-gray-700">
              Phường/Xã (công ty) <span class="text-red-500">*</span>
            </label>
            <select
              ref="company_ward_id"
              v-model="form.company_ward_id"
              :class="[
                'w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400',
                errors.company_ward_id ? 'border-red-500' : 'border-gray-300'
              ]"
            >
              <option value="">-- Chọn phường --</option>
              <option v-for="item in wards" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <p v-if="errors.company_ward_id" class="text-sm text-red-600 mt-1">
              {{ errors.company_ward_id[0] }}
            </p>
          </div>

        </div>
      </template>
      <!-- 💵 Công nợ -->
      <div>
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Công nợ & Ghi chú</h3>
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
          <div class="flex items-center gap-2 mt-4">
            <input type="checkbox" v-model="form.marketing_consent" class="w-5 h-5 text-blue-600" />
            <label class="text-sm text-gray-700">Đồng ý nhận marketing</label>
          </div>
          <div class="mt-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700">Mã giới thiệu</label>
            <input v-model="form.referral_code" type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>
        <div class="mt-4">
          <label class="block mb-1 text-sm font-semibold text-gray-700">Ghi chú</label>
          <textarea v-model="form.note" rows="3"
            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
        </div>
      </div>
      <!-- 🔘 Submit -->
      <div class="pt-4 mt-6">
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
  props: {
    mode: {
      type: String,
      default: 'create'
    },
    customerId: {
      type: [String, Number],
      default: null
    }
  },
  data() {
    return {
      form: {
        // --- Thông tin cơ bản ---
        name: '',
        phone: '',
        code: '',
        email: '',
        birthday: '',
        gender: '',
        address: '',
        province_id: '',
        district_id: '',
        ward_id: '',

        // --- Liên hệ ---
        facebook_url: '',
        zalo_phone: '',
        source: '',
        preferred_contact: '',
        last_contacted_at: '',
        rating: '',

        // --- UTM tracking ---
        utm_source: '',
        utm_medium: '',
        utm_campaign: '',

        // --- Phân loại ---
        type: 'individual', // hoặc 'company'
        status: 'active',
        assigned_user_id: '',
        customer_group_id: '',
        customer_stage: 'new',
        contact_frequency_days: '',

        // --- Thông tin công ty (chỉ khi type = company) ---
        company_name: '',
        position: '',
        website_url: '',
        number_of_employees: '',
        revenue_estimate: '',
        tax_code: '',
        founded_at: '',
        representative_name: '',
        company_address: '',
        company_province_id: '',
        company_district_id: '',
        company_ward_id: '',
        company_tax_code: '',

        // --- Công nợ & ghi chú ---
        debt_amount: '',
        credit_limit: '',
        marketing_consent: true,
        referral_code: '',
        note: '',
      },

      // Avatar
      avatarFile: null,
      avatarPreview: null,

      // Bản gốc dùng để kiểm tra dirty form
      originalForm: null,

      // Validation error
      errors: {},
      companyDistricts: [],
      companyWards: [],
      provinces: [],
      districts: [],
      wards: [],
      customerGroups : []
    }
  },
  async mounted() {
    if (this.mode === 'update' && this.customerId) {
      try {
        const customer = await window.axios.get(`/api/customer/detail/${this.customerId}`)
        const { data } = customer.data
        this.form = { ...this.form, ...data }
        this.form.avatar = null;
        this.form.birthday = data.birthday ? data.birthday.split('T')[0] : ''
        this.avatarPreview = data.avatar_url || null
        this.originalForm = JSON.stringify(this.form)
      } catch (err) {
        alert('Không tìm thấy khách hàng')
        this.$router.push('/sale/customer')
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
      this.$refs.avatarInput.value = null
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
      this.errors = {}

      if (!this.form.name) this.errors.name = ['Tên khách hàng là bắt buộc']
      if (!this.form.phone) this.errors.phone = ['SĐT là bắt buộc']
      if (!this.form.code) this.errors.code = ['Mã KH là bắt buộc']
      if (this.form.type === 'company') {
        if (!this.form.company_name) this.errors.company_name = ['Tên công ty là bắt buộc']
        if (!this.form.representative_name) this.errors.representative_name = ['Người đại diện là bắt buộc']
        if (!this.form.company_address) this.errors.company_address = ['Địa chỉ công ty là bắt buộc']
        if (!this.form.company_province_id) this.errors.company_province_id = ['Tỉnh/Thành công ty là bắt buộc']
        if (!this.form.company_district_id) this.errors.company_district_id = ['Quận/Huyện công ty là bắt buộc']
        if (!this.form.company_ward_id) this.errors.company_ward_id = ['Phường/Xã công ty là bắt buộc']
      }
      if (Object.keys(this.errors).length > 0) {
        const firstErrorKey = Object.keys(this.errors)[0]
        this.$nextTick(() => {
          const el = this.$refs[firstErrorKey]
          if (el && el.scrollIntoView) {
              el.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
              })
          }
        })
        return
      }
      try {
        const formData = new FormData()
        for (const key in this.form) {
          formData.append(key, this.form[key] ?? '')
        }
        if (this.avatarFile) {
          formData.append('avatar', this.avatarFile)
        }

        if (this.mode === 'update') {
          await window.axios.post(`/api/customer/update/${this.customerId}`, formData)
        } else {
          await window.axios.post('/api/customer/create', formData)
        }

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