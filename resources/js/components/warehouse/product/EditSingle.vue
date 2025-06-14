<template>
  <Form :mode="'update'" :type="'single'" :id="Number($route.params.id)" />
</template>

<script>
import Form from './Form.vue'

export default {
  components: { Form }
}
</script>
