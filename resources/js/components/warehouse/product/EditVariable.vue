<template>
    <div class="p-4 bg-white rounded-xl shadow-md">
      <Form :mode="'update'" :type="'variable'" :id="$route.params.id" />
    </div>
</template>

<script>
import Form from './Form.vue'

export default {
  components: { Form }
}
</script>
