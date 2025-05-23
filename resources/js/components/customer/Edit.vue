<template>
  <CustomerForm :mode="'update'" :customerId="$route.params.id" />
</template>

<script>
import CustomerForm from './From.vue'

export default {
  components: { CustomerForm }
}
</script>
