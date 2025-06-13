<template>
  <Form :mode="'update'" :customerId="Number($route.params.id)" />
</template>

<script>
import Form from './From.vue'

export default {
  components: { Form }
}
</script>
