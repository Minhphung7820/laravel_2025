<template>
  <Form :mode="'update'" :customerId="$route.params.id" />
</template>

<script>
import Form from './From.vue'

export default {
  components: { Form }
}
</script>
