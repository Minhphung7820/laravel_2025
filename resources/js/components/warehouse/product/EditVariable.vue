<template>
  <Form :mode="'update'" :type="'variable'" :id="Number($route.params.id)" />
</template>

<script>
import Form from './Form.vue'

export default {
  components: { Form }
}
</script>
