<template>
  <Form :mode="'update'" :type="'combo'" :id="Number($route.params.id)" />
</template>

<script>
import Form from './Form.vue'

export default {
  components: { Form }
}
</script>
