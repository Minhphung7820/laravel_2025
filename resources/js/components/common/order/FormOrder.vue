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
      <TableAddItems
        :form="form"
        :transaction_type="transaction_type"
        @add-items="handleAddItems"
      />
    </div>
  </div>
</template>

<script>
import TableAddItems from '@/components/common/order/TableAddItems.vue'

export default {
  components: {TableAddItems},
  props: {
    mode: {
      type: String,
      required: true
    },
    transaction_type: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      form: {
        seller_email: 'richchoi@gmail.com',
        created_at: this.today(),
        order_date: this.today(),
        email: '',
        customer_id: 1,
        phone: '',
        note: '',
        items: []
      },
      customers : [
        { id: 1, name: 'Khách A' },
        { id: 2, name: 'Khách B' }
      ]
    }
  },
  methods: {
    handleAddItems(items) {
      this.form.items.push(...items)
    },
    today() {
      const d = new Date()
      return d.toISOString().slice(0, 10)
    }
  }
}
</script>

<style scoped>
</style>