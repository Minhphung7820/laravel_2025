<template>
  <div class="space-y-6 p-8 bg-white rounded-xl shadow-md">
    <h1 class="text-2xl font-bold text-gray-800">Thông tin đơn bán hàng</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block font-semibold">Nhân viên bán hàng</label>
        <input
          type="text"
          v-model="form.seller_email"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100"
          disabled
        />
      </div>

      <div>
        <label class="block font-semibold">Ngày tạo đơn</label>
        <input
          type="text"
          v-model="form.created_at"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100"
          disabled
        />
      </div>

      <div>
        <label class="block font-semibold">Ngày đặt hàng</label>
        <input
          type="date"
          v-model="form.order_date"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block font-semibold">Email</label>
        <input
          type="email"
          v-model="form.email"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block font-semibold">Khách hàng</label>
        <select
          v-model="form.customer_id"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option disabled value="">Vui lòng chọn</option>
          <option v-for="customer in customers" :key="customer.id" :value="customer.id">
            {{ customer.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block font-semibold">Số điện thoại</label>
        <input
          type="text"
          v-model="form.phone"
          class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <div>
      <label class="block font-semibold">Ghi chú</label>
      <textarea
        v-model="form.note"
        rows="4"
        class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      ></textarea>
    </div>

    <div>
      <h2 class="block font-semibold">Sản phẩm</h2>

      <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left px-4 py-2 border-b">SKU</th>
              <th class="text-left px-4 py-2 border-b">Tên sản phẩm</th>
              <th class="text-left px-4 py-2 border-b">Số lượng</th>
              <th class="text-left px-4 py-2 border-b">Đơn giá</th>
              <th class="text-left px-4 py-2 border-b">Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.products" :key="index" class="border-t">
              <td class="px-4 py-2">{{ item.sku }}</td>
              <td class="px-4 py-2">{{ item.name }}</td>
              <td class="px-4 py-2">{{ item.quantity }}</td>
              <td class="px-4 py-2">{{ formatCurrency(item.unit_price) }}</td>
              <td class="px-4 py-2">{{ formatCurrency(item.total) }}</td>
            </tr>
            <tr v-if="!form.products.length">
              <td colspan="5" class="text-center px-4 py-6 text-gray-500">Không có sản phẩm</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SellForm',
  props: {
    form: {
      type: Object,
      required: true
    },
    customers: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    formatCurrency(value) {
      if (!value && value !== 0) return ''
      return Number(value).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
    }
  }
}
</script>

<style scoped>
</style>