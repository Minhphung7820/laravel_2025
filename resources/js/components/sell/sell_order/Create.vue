<template>
    <SellForm :mode="'create'" :transaction_type="'sell'" />
</template>

<script>
import SellForm from '@/components/common/order/FormOrder.vue'

export default {
  components: { SellForm },
}
</script>
