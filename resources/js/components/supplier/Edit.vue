<template>
  <Form :mode="'update'" :supplierId="$route.params.id" />
</template>

<script>
import Form from './From.vue'

export default {
  components: { Form }
}
</script>
