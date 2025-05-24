<template>
  <Form mode="update" :id="parseInt($route.params.id)" />
</template>

<script>
import Form from './Form.vue'
export default { components: { Form } }
</script>
