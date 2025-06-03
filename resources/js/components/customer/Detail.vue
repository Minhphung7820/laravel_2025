<template>
  <div class="p-6 w-full bg-white rounded-2xl shadow-xl">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-gray-800 border-b pb-2">
        👤 Thông tin khách hàng
      </h2>
      <router-link
        :to="`/sale/customer/${customer.id}/edit`"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm"
      >
        ✏️ Sửa
      </router-link>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
      <!-- Avatar -->
      <div class="flex flex-col items-center md:items-start">
        <img
          v-if="customer.avatar_url"
          :src="customer.avatar_url"
          alt="Avatar"
          class="w-40 h-40 rounded-full object-cover border border-gray-300 shadow-sm"
        />
        <div
          v-else
          class="w-40 h-40 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center text-gray-400 text-sm"
        >
          Không có ảnh
        </div>
      </div>

      <!-- Customer Details -->
      <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-4 text-gray-700 text-sm">
        <div><span class="font-semibold">Tên:</span> {{ customer.name }}</div>
        <div><span class="font-semibold">Số điện thoại:</span> {{ customer.phone }}</div>
        <div><span class="font-semibold">Email:</span> {{ customer.email || '---' }}</div>
        <div><span class="font-semibold">Ngày sinh:</span> {{ customer.birthday || '---' }}</div>
        <div><span class="font-semibold">Giới tính:</span> {{ formatGender(customer.gender) }}</div>
        <div><span class="font-semibold">Loại khách hàng:</span> {{ customer.type }}</div>
        <div><span class="font-semibold">Trạng thái:</span> {{ customer.status }}</div>
        <div><span class="font-semibold">Nguồn khách:</span> {{ customer.source }}</div>
        <div><span class="font-semibold">Facebook:</span>
          <a
            :href="customer.facebook_url"
            v-if="customer.facebook_url"
            class="text-blue-600 hover:underline"
            target="_blank"
          >{{ customer.facebook_url }}</a>
          <span v-else>---</span>
        </div>
        <div><span class="font-semibold">Zalo:</span> {{ customer.zalo_phone || '---' }}</div>
        <div><span class="font-semibold">Địa chỉ:</span> {{ customer.address || '---' }}</div>
        <div><span class="font-semibold">Mã số thuế:</span> {{ customer.tax_code || '---' }}</div>
        <div><span class="font-semibold">Hạn mức tín dụng:</span> {{ formatCurrency(customer.credit_limit) }}</div>
        <div><span class="font-semibold">Công nợ:</span> {{ formatCurrency(customer.debt_amount) }}</div>
        <div><span class="font-semibold">Tổng chi tiêu:</span> {{ formatCurrency(customer.total_spent) }}</div>
        <div class="sm:col-span-2">
          <span class="font-semibold">Ghi chú:</span><br />
          <span class="whitespace-pre-line">{{ customer.note || '---' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios'

export default {
  data() {
    return {
      customer: {},
    }
  },
  mounted() {
    this.fetchCustomer()
  },
  methods: {
    async fetchCustomer() {
      const id = this.$route.params.id
      try {
        const res = await axios.get(`/api/customer/detail/${id}`)
        this.customer = res.data.data
      } catch (error) {
        console.error('Lỗi khi tải chi tiết khách hàng:', error)
      }
    },
    formatCurrency(value) {
      const number = Number(value)
      if (!number || isNaN(number)) return '0 ₫'
      return number.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
    },
    formatGender(value) {
      switch (value) {
        case 'male':
          return 'Nam'
        case 'female':
          return 'Nữ'
        default:
          return 'Không rõ'
      }
    },
  },
}
</script>

<style scoped>
</style>
