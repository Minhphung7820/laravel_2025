<template>
  <div>
    <SellForm :form="form" :customers="customers" />

    <!-- Các phần khác như bảng sản phẩm... -->
  </div>
</template>

<script>
import SellForm from '@/components/common/order/FormOrder.vue'

export default {
  components: { SellForm },
  data() {
    return {
      form: {
        seller_email: 'richchoi@gmail.com',
        created_at: this.today(),
        order_date: this.today(),
        email: '',
        customer_id: '',
        phone: '',
        note: '',
        products: []
      },
      customers: []
    }
  },
  mounted() {
    // fake API
    this.customers = [
      { id: 1, name: 'Khách A' },
      { id: 2, name: 'Khách B' }
    ]
  },
  methods: {
    today() {
      const d = new Date()
      return d.toISOString().slice(0, 10)
    }
  }
}
</script>
