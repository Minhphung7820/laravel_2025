<template>
    <PriceQuoteForm :mode="'create'" :transaction_type="'price_quote'" />
</template>

<script>
import PriceQuoteForm from '@/components/common/order/FormOrder.vue'

export default {
  components: { PriceQuoteForm }
}
</script>
